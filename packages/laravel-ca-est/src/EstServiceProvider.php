<?php

declare(strict_types=1);

namespace CA\Est;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Csr\Contracts\CsrManagerInterface;
use CA\Est\Console\Commands\EstCleanupCommand;
use CA\Est\Console\Commands\EstEnrollmentListCommand;
use CA\Est\Console\Commands\EstSetupCommand;
use CA\Est\Contracts\EstServerInterface;
use CA\Est\Http\Middleware\EstAuthentication;
use CA\Est\Http\Middleware\EstContentType;
use CA\Est\Services\EstAuthenticator;
use CA\Est\Services\EstResponseBuilder;
use CA\Est\Services\EstServer;
use CA\Key\Contracts\KeyManagerInterface;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class EstServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-est.php',
            'ca-est',
        );

        $this->app->singleton(EstResponseBuilder::class);
        $this->app->singleton(EstAuthenticator::class);

        $this->app->singleton(EstServerInterface::class, function ($app): EstServer {
            return new EstServer(
                certificateManager: $app->make(CertificateManagerInterface::class),
                csrManager: $app->make(CsrManagerInterface::class),
                keyManager: $app->make(KeyManagerInterface::class),
                responseBuilder: $app->make(EstResponseBuilder::class),
                authenticator: $app->make(EstAuthenticator::class),
            );
        });

        $this->app->alias(EstServerInterface::class, 'ca-est');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-est.php' => config_path('ca-est.php'),
            ], 'ca-est-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-est-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                EstSetupCommand::class,
                EstEnrollmentListCommand::class,
                EstCleanupCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (!config('ca-est.enabled', true)) {
            return;
        }

        Route::prefix(config('ca-est.route_prefix', '.well-known/est'))
            ->middleware(array_merge(
                config('ca-est.middleware', ['api']),
                [
                    EstAuthentication::class,
                    EstContentType::class,
                ],
            ))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
