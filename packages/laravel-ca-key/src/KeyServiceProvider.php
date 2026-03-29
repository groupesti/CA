<?php

declare(strict_types=1);

namespace CA\Key;

use CA\Contracts\EncryptionStrategyInterface;
use CA\Key\Console\Commands\KeyExportCommand;
use CA\Key\Console\Commands\KeyGenerateCommand;
use CA\Key\Console\Commands\KeyListCommand;
use CA\Key\Console\Commands\KeyRotateCommand;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Key\Generators\EcdsaKeyGenerator;
use CA\Key\Generators\Ed25519KeyGenerator;
use CA\Key\Generators\RsaKeyGenerator;
use CA\Key\Services\KeyExporter;
use CA\Key\Services\KeyGenerator;
use CA\Key\Services\KeyManager;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class KeyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-key.php',
            'ca-key',
        );

        $this->app->singleton(KeyGenerator::class, function ($app): KeyGenerator {
            $factory = new KeyGenerator();

            $factory->register(new RsaKeyGenerator(
                defaultBits: (int) config('ca-key.default_rsa_bits', 4096),
            ));
            $factory->register(new EcdsaKeyGenerator());
            $factory->register(new Ed25519KeyGenerator());

            return $factory;
        });

        $this->app->singleton(KeyExporter::class);

        $this->app->singleton(KeyManagerInterface::class, function ($app): KeyManager {
            return new KeyManager(
                keyGenerator: $app->make(KeyGenerator::class),
                keyExporter: $app->make(KeyExporter::class),
                encryptionStrategy: $app->make(EncryptionStrategyInterface::class),
            );
        });

        $this->app->alias(KeyManagerInterface::class, 'ca-key');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-key.php' => config_path('ca-key.php'),
            ], 'ca-key-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-key-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                KeyGenerateCommand::class,
                KeyListCommand::class,
                KeyExportCommand::class,
                KeyRotateCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (! config('ca-key.routes.enabled', true)) {
            return;
        }

        Route::prefix(config('ca-key.routes.prefix', 'api/ca/keys'))
            ->middleware(config('ca-key.routes.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
