<?php

namespace App\Console\Commands;

use App\Models\AudioMessage;
use App\Models\DoctorReel;
use App\Models\GreetingCard;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use RuntimeException;

class MigrateMediaToS3 extends Command
{
    protected $signature = 'media:migrate-to-s3 {--delete-local : Delete each local file after its S3 copy is verified}';

    protected $description = 'Copy existing campaign media from local storage to S3 and persist its S3 URL';

    public function handle(): int
    {
        $local = Storage::disk('local');
        $s3 = Storage::disk('s3');
        $copied = 0;
        $missing = 0;

        $mappings = [
            [DoctorReel::query(), ['original_video' => ['original_video_url', 'original/video'], 'original_audio' => ['original_audio_url', 'original/audio'], 'generated_video' => ['generated_video_url', 'videos'], 'generated_card' => ['generated_card_url', 'cards']]],
            [AudioMessage::query(), ['original_audio' => ['original_audio_url', 'original/audio'], 'generated_video' => ['generated_video_url', 'audios']]],
            [GreetingCard::query(), ['generated_card' => ['generated_card_url', 'cards']]],
        ];

        foreach ($mappings as [$query, $fields]) {
            $query->chunkById(100, function ($models) use ($fields, $local, $s3, &$copied, &$missing): void {
                foreach ($models as $model) {
                    foreach ($fields as $pathField => [$urlField, $prefix]) {
                        $sourcePath = $model->{$pathField};
                        if (! $sourcePath) {
                            continue;
                        }
                        $path = $this->targetPath($sourcePath, $prefix);

                        if (! $s3->exists($path)) {
                            $sourceDisk = $local->exists($sourcePath) ? $local : ($s3->exists($sourcePath) ? $s3 : null);
                            if ($sourceDisk === null) {
                                $this->warn("Missing local media: {$sourcePath}");
                                $missing++;
                                continue;
                            }

                            $stream = $sourceDisk->readStream($sourcePath);
                            if ($stream === false || ! $s3->put($path, $stream)) {
                                throw new RuntimeException("Unable to upload {$path} to S3.");
                            }
                            fclose($stream);
                            $copied++;
                        }

                        $this->saveMediaLocation($model, $pathField, $path, $urlField, $s3->url($path));

                        if ($this->option('delete-local') && $s3->exists($path)) {
                            $local->delete($sourcePath);
                        }
                    }
                }
            });
        }

        $this->info("S3 migration complete: {$copied} copied, {$missing} missing.");

        return self::SUCCESS;
    }

    private function saveMediaLocation(Model $model, string $pathField, string $path, string $urlField, string $url): void
    {
        if ($model->{$pathField} !== $path || $model->{$urlField} !== $url) {
            $model->forceFill([$pathField => $path, $urlField => $url])->saveQuietly();
        }
    }

    private function targetPath(string $sourcePath, string $prefix): string
    {
        $project = trim((string) config('filesystems.media_prefix', 'Teachers-Day'), '/');

        return ($project !== '' ? $project.'/' : '').$prefix.'/'.basename($sourcePath);
    }
}
