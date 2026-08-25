<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class MediaStorage
{
    public function diskName(): string
    {
        return (string) config('filesystems.media', 'local');
    }

    public function disk(): FilesystemAdapter
    {
        return Storage::disk($this->diskName());
    }

    public function path(string $path): string
    {
        $path = ltrim(str_replace('\\', '/', $path), '/');
        $prefix = trim((string) config('filesystems.media_prefix', 'Teachers-Day'), '/');

        return $prefix !== '' && ! str_starts_with($path, $prefix.'/') ? $prefix.'/'.$path : $path;
    }

    public function storeUploaded(UploadedFile $file, string $directory, string $filename): string
    {
        return $file->storeAs($this->path($directory), $filename, $this->diskName());
    }

    public function localPath(string $path): string
    {
        if ($this->diskName() === 'local') {
            return $this->disk()->path($path);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $temporary = tempnam(sys_get_temp_dir(), 'teacher-media-');
        if ($temporary === false) {
            throw new RuntimeException('Unable to create a temporary media file.');
        }
        $localPath = $extension ? $temporary.'.'.$extension : $temporary;
        if ($localPath !== $temporary) {
            rename($temporary, $localPath);
        }

        $source = $this->disk()->readStream($path);
        $destination = fopen($localPath, 'wb');
        if ($source === false || $destination === false) {
            throw new RuntimeException('Unable to read media from storage.');
        }
        stream_copy_to_stream($source, $destination);
        fclose($source);
        fclose($destination);

        return $localPath;
    }

    public function outputPath(string $path): string
    {
        if ($this->diskName() === 'local') {
            $this->disk()->makeDirectory(dirname($path));
            return $this->disk()->path($path);
        }

        $extension = pathinfo($path, PATHINFO_EXTENSION);
        $temporary = tempnam(sys_get_temp_dir(), 'teacher-output-');
        if ($temporary === false) {
            throw new RuntimeException('Unable to create a temporary output file.');
        }
        $localPath = $extension ? $temporary.'.'.$extension : $temporary;
        if ($localPath !== $temporary) {
            rename($temporary, $localPath);
        }

        return $localPath;
    }

    public function publish(string $localPath, string $path): void
    {
        if ($this->diskName() === 'local') {
            return;
        }
        $stream = fopen($localPath, 'rb');
        if ($stream === false || ! $this->disk()->put($path, $stream)) {
            throw new RuntimeException('Unable to publish generated media.');
        }
        fclose($stream);
        @unlink($localPath);
    }

    public function cleanupLocalCopy(string $localPath): void
    {
        if ($this->diskName() !== 'local' && str_starts_with(basename($localPath), 'teacher-media-')) {
            @unlink($localPath);
        }
    }

    public function download(string $path, string $filename): StreamedResponse
    {
        return $this->disk()->download($path, $filename);
    }

    public function url(string $path): string
    {
        return $this->disk()->url($path);
    }

    public function stream(string $path, string $contentType): StreamedResponse
    {
        return response()->stream(function () use ($path): void {
            $stream = $this->disk()->readStream($path);
            if ($stream !== false) {
                fpassthru($stream);
                fclose($stream);
            }
        }, 200, [
            'Content-Type' => $contentType,
            'Cache-Control' => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma' => 'no-cache',
            'Expires' => '0',
        ]);
    }
}
