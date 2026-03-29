<?php

declare(strict_types=1);

namespace CA\Storage;

use CA\Contracts\StorageDriverInterface;
use CA\Exceptions\StorageException;
use Illuminate\Support\Facades\DB;

final class DatabaseDriver implements StorageDriverInterface
{
    public function __construct(
        private readonly ?string $connection,
        private readonly string $table = 'ca_storage',
    ) {}

    public function store(string $identifier, string $content, string $format): string
    {
        $path = $this->getPath($identifier, $format);

        try {
            $this->getQuery()->updateOrInsert(
                [
                    'identifier' => $identifier,
                    'format' => $format,
                ],
                [
                    'content' => base64_encode($content),
                    'path' => $path,
                    'updated_at' => now(),
                    'created_at' => now(),
                ],
            );
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
        $record = $this->getQuery()
            ->where('identifier', $identifier)
            ->where('format', $format)
            ->first();

        if ($record === null) {
            return null;
        }

        $decoded = base64_decode($record->content, strict: true);

        if ($decoded === false) {
            throw new StorageException("Failed to decode stored content for identifier [{$identifier}].");
        }

        return $decoded;
    }

    public function exists(string $identifier, string $format): bool
    {
        return $this->getQuery()
            ->where('identifier', $identifier)
            ->where('format', $format)
            ->exists();
    }

    public function delete(string $identifier, string $format): bool
    {
        $deleted = $this->getQuery()
            ->where('identifier', $identifier)
            ->where('format', $format)
            ->delete();

        return $deleted > 0;
    }

    public function getPath(string $identifier, string $format): string
    {
        return "db://{$this->table}/{$identifier}.{$format}";
    }

    private function getQuery(): \Illuminate\Database\Query\Builder
    {
        return DB::connection($this->connection)->table($this->table);
    }
}
