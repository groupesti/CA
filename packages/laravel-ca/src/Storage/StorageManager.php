<?php

declare(strict_types=1);

namespace CA\Storage;

use CA\Contracts\StorageDriverInterface;
use CA\Exceptions\StorageException;
use Illuminate\Contracts\Foundation\Application;

final class StorageManager
{
    /** @var array<string, StorageDriverInterface> */
    private array $drivers = [];

    public function __construct(
        private readonly Application $app,
    ) {}

    public function driver(?string $name = null): StorageDriverInterface
    {
        $name ??= $this->getDefaultDriver();

        if (isset($this->drivers[$name])) {
            return $this->drivers[$name];
        }

        $this->drivers[$name] = $this->resolve($name);

        return $this->drivers[$name];
    }

    public function getDefaultDriver(): string
    {
        return $this->app['config']->get('ca.storage.default', 'database');
    }

    private function resolve(string $name): StorageDriverInterface
    {
        $config = $this->app['config']->get("ca.storage.drivers.{$name}");

        if ($config === null) {
            throw new StorageException("Storage driver [{$name}] is not configured.");
        }

        return match ($config['driver']) {
            'database' => new DatabaseDriver(
                connection: $config['connection'],
                table: $config['table'] ?? 'ca_storage',
            ),
            'filesystem' => new FilesystemDriver(
                disk: $config['disk'] ?? 'local',
                basePath: $config['base_path'] ?? 'ca-storage',
            ),
            default => throw new StorageException("Unsupported storage driver [{$config['driver']}]."),
        };
    }
}
