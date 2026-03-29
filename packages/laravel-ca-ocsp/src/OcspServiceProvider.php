<?php

declare(strict_types=1);

namespace CA\OCSP;

use CA\Key\Contracts\KeyManagerInterface;
use CA\OCSP\Asn1\CertIdParser;
use CA\OCSP\Asn1\OcspRequestParser;
use CA\OCSP\Asn1\OcspResponseBuilder;
use CA\OCSP\Console\Commands\OcspSetupCommand;
use CA\OCSP\Console\Commands\OcspStatusCommand;
use CA\OCSP\Contracts\CertificateStatusResolverInterface;
use CA\OCSP\Contracts\OcspResponderInterface;
use CA\OCSP\Services\CertificateStatusResolver;
use CA\OCSP\Services\OcspResponder;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OcspServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-ocsp.php',
            'ca-ocsp',
        );

        $this->app->singleton(CertIdParser::class);
        $this->app->singleton(OcspRequestParser::class);
        $this->app->singleton(OcspResponseBuilder::class);

        $this->app->singleton(CertificateStatusResolverInterface::class, function ($app): CertificateStatusResolver {
            return new CertificateStatusResolver(
                certIdParser: $app->make(CertIdParser::class),
            );
        });

        $this->app->singleton(OcspResponderInterface::class, function ($app): OcspResponder {
            return new OcspResponder(
                requestParser: $app->make(OcspRequestParser::class),
                responseBuilder: $app->make(OcspResponseBuilder::class),
                statusResolver: $app->make(CertificateStatusResolverInterface::class),
                keyManager: $app->make(KeyManagerInterface::class),
            );
        });

        $this->app->alias(OcspResponderInterface::class, 'ca-ocsp');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-ocsp.php' => config_path('ca-ocsp.php'),
            ], 'ca-ocsp-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-ocsp-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                OcspSetupCommand::class,
                OcspStatusCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (!config('ca-ocsp.enabled', true)) {
            return;
        }

        $middleware = config('ca-ocsp.middleware', []);

        Route::prefix(config('ca-ocsp.route_prefix', 'ocsp'))
            ->middleware($middleware)
            ->group(__DIR__ . '/../routes/api.php');
    }
}
