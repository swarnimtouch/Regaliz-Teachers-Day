<?php

namespace App\Services;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
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
        return $this->disk()->download($path, $filename, [
            'Content-Type' => $this->disk()->mimeType($path) ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
            'Cache-Control' => 'private, no-store',
        ]);
    }

    public function url(string $path): string
    {
        return $this->disk()->url($path);
    }

    public function stream(string $path, string $contentType, ?Request $request = null): StreamedResponse
    {
        $size = $this->disk()->size($path);
        $start = 0;
        $end = max(0, $size - 1);
        $status = 200;
        $range = $request?->header('Range');

        if ($range && preg_match('/^bytes=(\d*)-(\d*)$/', trim($range), $matches)) {
            if ($matches[1] === '' && $matches[2] !== '') {
                $start = max(0, $size - (int) $matches[2]);
            } else {
                $start = (int) $matches[1];
                $end = $matches[2] !== '' ? min((int) $matches[2], $size - 1) : $end;
            }

            if ($start >= $size || $start > $end) {
                return response()->stream(static function (): void {}, 416, [
                    'Content-Range' => 'bytes */'.$size,
                    'Accept-Ranges' => 'bytes',
                ]);
            }
            $status = 206;
        }

        $length = $end - $start + 1;
        return response()->stream(function () use ($path, $start, $length): void {
            $stream = $this->disk()->readStream($path);
            if ($stream !== false) {
                if ($start > 0) {
                    if (@fseek($stream, $start) !== 0) {
                        stream_get_contents($stream, $start);
                    }
                }
                $remaining = $length;
                while ($remaining > 0 && ! feof($stream)) {
                    $chunk = fread($stream, min(1024 * 1024, $remaining));
                    if ($chunk === false || $chunk === '') break;
                    echo $chunk;
                    $remaining -= strlen($chunk);
                }
                fclose($stream);
            }
        }, $status, [
            'Content-Type' => $contentType,
            'Content-Length' => (string) $length,
            'Accept-Ranges' => 'bytes',
            'Content-Range' => $status === 206 ? "bytes {$start}-{$end}/{$size}" : null,
            'Cache-Control' => 'private, max-age=3600',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }
}
