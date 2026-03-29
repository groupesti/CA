<?php

declare(strict_types=1);

namespace CA\Tsa;

use CA\Key\Contracts\KeyManagerInterface;
use CA\Tsa\Console\Commands\TsaSetupCommand;
use CA\Tsa\Console\Commands\TsaStatusCommand;
use CA\Tsa\Console\Commands\TsaTokenListCommand;
use CA\Tsa\Console\Commands\TsaVerifyCommand;
use CA\Tsa\Contracts\TsaServerInterface;
use CA\Tsa\Contracts\TsaVerifierInterface;
use CA\Tsa\Services\TsaRequestParser;
use CA\Tsa\Services\TsaResponseBuilder;
use CA\Tsa\Services\TsaSerialGenerator;
use CA\Tsa\Services\TsaServer;
use CA\Tsa\Services\TsaVerifier;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class TsaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-tsa.php',
            'ca-tsa',
        );

        $this->app->singleton(TsaSerialGenerator::class);
        $this->app->singleton(TsaRequestParser::class);
        $this->app->singleton(TsaResponseBuilder::class);

        $this->app->singleton(TsaServerInterface::class, function ($app): TsaServer {
            return new TsaServer(
                requestParser: $app->make(TsaRequestParser::class),
                responseBuilder: $app->make(TsaResponseBuilder::class),
                serialGenerator: $app->make(TsaSerialGenerator::class),
                keyManager: $app->make(KeyManagerInterface::class),
            );
        });

        $this->app->singleton(TsaVerifierInterface::class, function ($app): TsaVerifier {
            return new TsaVerifier(
                requestParser: $app->make(TsaRequestParser::class),
            );
        });

        $this->app->alias(TsaServerInterface::class, 'ca-tsa');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-tsa.php' => config_path('ca-tsa.php'),
            ], 'ca-tsa-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-tsa-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                TsaSetupCommand::class,
                TsaStatusCommand::class,
                TsaVerifyCommand::class,
                TsaTokenListCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (!config('ca-tsa.enabled', true)) {
            return;
        }

        Route::prefix(config('ca-tsa.route_prefix', 'tsa'))
            ->middleware(config('ca-tsa.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
