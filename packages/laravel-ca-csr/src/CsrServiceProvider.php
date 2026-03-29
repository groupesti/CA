<?php

declare(strict_types=1);

namespace CA\Csr;

use CA\Csr\Console\Commands\CsrApproveCommand;
use CA\Csr\Console\Commands\CsrCreateCommand;
use CA\Csr\Console\Commands\CsrImportCommand;
use CA\Csr\Console\Commands\CsrListCommand;
use CA\Csr\Contracts\CsrManagerInterface;
use CA\Csr\Contracts\CsrValidatorInterface;
use CA\Csr\Services\CsrBuilder;
use CA\Csr\Services\CsrManager;
use CA\Csr\Services\CsrValidator;
use CA\Key\Contracts\KeyManagerInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CsrServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-csr.php',
            'ca-csr',
        );

        $this->app->singleton(CsrValidatorInterface::class, CsrValidator::class);

        $this->app->singleton(CsrManagerInterface::class, function ($app): CsrManager {
            return new CsrManager(
                keyManager: $app->make(KeyManagerInterface::class),
                validator: $app->make(CsrValidatorInterface::class),
            );
        });

        $this->app->bind(CsrBuilder::class, function ($app): CsrBuilder {
            return new CsrBuilder(
                manager: $app->make(CsrManagerInterface::class),
            );
        });

        $this->app->alias(CsrManagerInterface::class, 'ca-csr');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-csr.php' => config_path('ca-csr.php'),
            ], 'ca-csr-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-csr-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                CsrCreateCommand::class,
                CsrListCommand::class,
                CsrImportCommand::class,
                CsrApproveCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (!config('ca-csr.routes.enabled', true)) {
            return;
        }

        Route::prefix(config('ca-csr.routes.prefix', 'api/ca/csrs'))
            ->middleware(config('ca-csr.routes.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
