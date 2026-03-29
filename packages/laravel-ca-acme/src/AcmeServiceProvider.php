<?php

declare(strict_types=1);

namespace CA\Acme;

use CA\Acme\Console\Commands\AcmeAccountListCommand;
use CA\Acme\Console\Commands\AcmeCleanupCommand;
use CA\Acme\Console\Commands\AcmeOrderListCommand;
use CA\Acme\Console\Commands\AcmeSetupCommand;
use CA\Acme\Contracts\AcmeServerInterface;
use CA\Acme\Contracts\ChallengeValidatorInterface;
use CA\Acme\Services\AcmeServer;
use CA\Acme\Services\Challenges\Dns01Validator;
use CA\Acme\Services\Challenges\Http01Validator;
use CA\Acme\Services\Challenges\TlsAlpn01Validator;
use CA\Acme\Services\JwsVerifier;
use CA\Acme\Services\NonceManager;
use CA\Acme\Order\OrderProcessor;
use Illuminate\Support\ServiceProvider;

class AcmeServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-acme.php',
            'ca-acme',
        );

        $this->app->singleton(JwsVerifier::class);
        $this->app->singleton(NonceManager::class);
        $this->app->singleton(OrderProcessor::class);

        $this->app->singleton(AcmeServerInterface::class, AcmeServer::class);
        $this->app->alias(AcmeServerInterface::class, 'ca-acme');

        // Register challenge validators
        $this->app->bind('acme.validator.http-01', Http01Validator::class);
        $this->app->bind('acme.validator.dns-01', Dns01Validator::class);
        $this->app->bind('acme.validator.tls-alpn-01', TlsAlpn01Validator::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        if (!config('ca-acme.enabled', true)) {
            return;
        }

        $this->loadRoutesFrom(__DIR__ . '/../routes/api.php');
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-acme.php' => config_path('ca-acme.php'),
            ], 'ca-acme-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'ca-acme-migrations');

            $this->commands([
                AcmeSetupCommand::class,
                AcmeAccountListCommand::class,
                AcmeOrderListCommand::class,
                AcmeCleanupCommand::class,
            ]);
        }
    }
}
