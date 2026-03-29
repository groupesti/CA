<?php

declare(strict_types=1);

namespace CA\Scep;

use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Csr\Contracts\CsrManagerInterface;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Scep\Console\Commands\ScepChallengeCommand;
use CA\Scep\Console\Commands\ScepCleanupCommand;
use CA\Scep\Console\Commands\ScepSetupCommand;
use CA\Scep\Console\Commands\ScepTransactionListCommand;
use CA\Scep\Contracts\ScepMessageParserInterface;
use CA\Scep\Contracts\ScepServerInterface;
use CA\Scep\Http\Middleware\ScepContentType;
use CA\Scep\Services\ScepChallengeManager;
use CA\Scep\Services\ScepMessageBuilder;
use CA\Scep\Services\ScepMessageParser;
use CA\Scep\Services\ScepServer;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class ScepServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-scep.php',
            'ca-scep',
        );

        $this->app->singleton(ScepChallengeManager::class);
        $this->app->singleton(ScepMessageBuilder::class);

        $this->app->singleton(ScepMessageParserInterface::class, function ($app): ScepMessageParser {
            return new ScepMessageParser(
                keyManager: $app->make(KeyManagerInterface::class),
            );
        });

        $this->app->singleton(ScepServerInterface::class, function ($app): ScepServer {
            return new ScepServer(
                messageParser: $app->make(ScepMessageParserInterface::class),
                messageBuilder: $app->make(ScepMessageBuilder::class),
                challengeManager: $app->make(ScepChallengeManager::class),
                certificateManager: $app->make(CertificateManagerInterface::class),
                csrManager: $app->make(CsrManagerInterface::class),
                keyManager: $app->make(KeyManagerInterface::class),
            );
        });

        $this->app->alias(ScepServerInterface::class, 'ca-scep');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-scep.php' => config_path('ca-scep.php'),
            ], 'ca-scep-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-scep-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                ScepSetupCommand::class,
                ScepChallengeCommand::class,
                ScepTransactionListCommand::class,
                ScepCleanupCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (!config('ca-scep.routes.enabled', true)) {
            return;
        }

        Route::prefix(config('ca-scep.route_prefix', 'scep'))
            ->middleware(array_merge(
                config('ca-scep.routes.middleware', []),
                [ScepContentType::class],
            ))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
