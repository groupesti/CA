<?php

declare(strict_types=1);

namespace CA\Storage;

use CA\Contracts\StorageDriverInterface;
use CA\Exceptions\StorageException;
use Illuminate\Support\Facades\Storage;

final class FilesystemDriver implements StorageDriverInterface
{
    public function __construct(
        private readonly string $disk,
        private readonly string $basePath,
    ) {}

    public function store(string $identifier, string $content, string $format): string
    {
        $path = $this->getPath($identifier, $format);

        try {
            $stored = Storage::disk($this->disk)->put($path, $content);

            if ($stored === false) {
                throw new StorageException("Failed to write to path [{$path}].");
            }
        } catch (StorageException $e) {
            throw $e;
        } catch (\Throwable $e) {
            throw new StorageException(
                "Failed to store content for identifier [{$identifier}]: {$e->getMessage()}",
                previous: $e,
            );
        }

        return $path;
    }

    public function retrieve(string $identifier, string $format): ?string
    {
        $path = $this->getPath($identifier, $format);

        if (!Storage::disk($this->disk)->exists($path)) {
            return null;
        }

        try {
            $content = Storage::disk($this->disk)->get($path);
        } catch (\Throwable $e) {
            throw new StorageException(
                "Failed to retrieve content for identifier [{$identifier}]: {$e->getMessage()}",
                previous: $e,
            );
        }

        return $content;
    }

    public function exists(string $identifier, string $format): bool
    {
        return Storage::disk($this->disk)->exists(
            $this->getPath($identifier, $format),
        );
    }

    public function delete(string $identifier, string $format): bool
    {
        $path = $this->getPath($identifier, $format);

        if (!Storage::disk($this->disk)->exists($path)) {
            return false;
        }

        return Storage::disk($this->disk)->delete($path);
    }

    public function getPath(string $identifier, string $format): string
    {
        return "{$this->basePath}/{$format}/{$identifier}.{$format}";
    }
}
