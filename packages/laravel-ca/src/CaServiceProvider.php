<?php

declare(strict_types=1);

namespace CA;

use CA\Console\Commands\CaInitCommand;
use CA\Console\Commands\CaListCommand;
use CA\Console\Commands\CaStatusCommand;
use CA\Console\Commands\SeedLookupsCommand;
use CA\Contracts\EncryptionStrategyInterface;
use CA\Encryption\LaravelEncryptionStrategy;
use CA\Encryption\PassphraseEncryptionStrategy;
use CA\Http\Middleware\CaAuthentication;
use CA\Services\CaManager;
use CA\Services\SerialNumberGenerator;
use CA\Storage\StorageManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca.php',
            'ca',
        );

        $this->registerStorageManager();
        $this->registerEncryptionStrategy();
        $this->registerCaManager();
    }

    public function boot(): void
    {
        $this->publishAssets();
        $this->loadMigrations();
        $this->registerCommands();
        $this->registerRoutes();
        $this->registerMiddleware();
    }

    private function registerStorageManager(): void
    {
        $this->app->singleton(StorageManager::class, function ($app): StorageManager {
            return new StorageManager($app);
        });
    }

    private function registerEncryptionStrategy(): void
    {
        $this->app->singleton(EncryptionStrategyInterface::class, function ($app): EncryptionStrategyInterface {
            $strategy = $app['config']->get('ca.encryption.default', 'laravel');

            return match ($strategy) {
                'passphrase' => new PassphraseEncryptionStrategy(),
                default => new LaravelEncryptionStrategy(),
            };
        });
    }

    private function registerCaManager(): void
    {
        $this->app->singleton(CaManager::class, function ($app): CaManager {
            return new CaManager(
                storageManager: $app->make(StorageManager::class),
                serialNumberGenerator: new SerialNumberGenerator(),
                encryptionStrategy: $app->make(EncryptionStrategyInterface::class),
            );
        });
    }

    private function publishAssets(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->publishes([
            __DIR__ . '/../config/ca.php' => config_path('ca.php'),
        ], 'ca-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'ca-migrations');

        $this->publishes([
            __DIR__ . '/../config/ca.php' => config_path('ca.php'),
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'ca');
    }

    private function loadMigrations(): void
    {
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');
    }

    private function registerCommands(): void
    {
        if (!$this->app->runningInConsole()) {
            return;
        }

        $this->commands([
            CaInitCommand::class,
            CaListCommand::class,
            CaStatusCommand::class,
            SeedLookupsCommand::class,
        ]);
    }

    private function registerRoutes(): void
    {
        if (!$this->app['config']->get('ca.api.enabled', true)) {
            return;
        }

        $prefix = $this->app['config']->get('ca.api.prefix', 'api/ca');
        $middleware = $this->app['config']->get('ca.api.middleware', ['api']);

        Route::prefix($prefix)
            ->middleware($middleware)
            ->group(__DIR__ . '/../routes/api.php');
    }

    private function registerMiddleware(): void
    {
        $router = $this->app['router'];
        $router->aliasMiddleware('ca.auth', CaAuthentication::class);
    }
}
