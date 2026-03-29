<?php

declare(strict_types=1);

namespace CA\Crt;

use CA\Crt\Console\Commands\CrtExportCommand;
use CA\Crt\Console\Commands\CrtExpiringScanCommand;
use CA\Crt\Console\Commands\CrtIssueCommand;
use CA\Crt\Console\Commands\CrtListCommand;
use CA\Crt\Console\Commands\CrtRenewCommand;
use CA\Crt\Console\Commands\CrtRevokeCommand;
use CA\Crt\Console\Commands\CrtVerifyCommand;
use CA\Crt\Contracts\CertificateManagerInterface;
use CA\Crt\Contracts\CertificateSignerInterface;
use CA\Crt\Contracts\CertificateValidatorInterface;
use CA\Crt\Services\CertificateExporter;
use CA\Crt\Services\CertificateManager;
use CA\Crt\Services\CertificateRenewer;
use CA\Crt\Services\CertificateSigner;
use CA\Crt\Services\CertificateValidator;
use CA\Crt\Services\ChainBuilder;
use CA\Key\Contracts\KeyManagerInterface;
use CA\Services\SerialNumberGenerator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class CrtServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/ca-crt.php',
            'ca-crt',
        );

        $this->app->singleton(ChainBuilder::class);
        $this->app->singleton(CertificateExporter::class);

        $this->app->singleton(CertificateSignerInterface::class, function ($app): CertificateSigner {
            return new CertificateSigner(
                serialGenerator: $app->make(SerialNumberGenerator::class),
            );
        });

        $this->app->singleton(CertificateValidatorInterface::class, CertificateValidator::class);

        $this->app->singleton(CertificateRenewer::class, function ($app): CertificateRenewer {
            return new CertificateRenewer(
                signer: $app->make(CertificateSignerInterface::class),
                keyManager: $app->make(KeyManagerInterface::class),
                serialGenerator: $app->make(SerialNumberGenerator::class),
                chainBuilder: $app->make(ChainBuilder::class),
            );
        });

        $this->app->singleton(CertificateManagerInterface::class, function ($app): CertificateManager {
            return new CertificateManager(
                signer: $app->make(CertificateSignerInterface::class),
                validator: $app->make(CertificateValidatorInterface::class),
                exporter: $app->make(CertificateExporter::class),
                renewer: $app->make(CertificateRenewer::class),
                chainBuilder: $app->make(ChainBuilder::class),
                keyManager: $app->make(KeyManagerInterface::class),
                serialGenerator: $app->make(SerialNumberGenerator::class),
            );
        });

        $this->app->alias(CertificateManagerInterface::class, 'ca-crt');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-crt.php' => config_path('ca-crt.php'),
            ], 'ca-crt-config');

            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'ca-crt-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                CrtIssueCommand::class,
                CrtListCommand::class,
                CrtRevokeCommand::class,
                CrtRenewCommand::class,
                CrtExportCommand::class,
                CrtVerifyCommand::class,
                CrtExpiringScanCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        if (!config('ca-crt.routes.enabled', true)) {
            return;
        }

        Route::prefix(config('ca-crt.routes.prefix', 'api/ca/certificates'))
            ->middleware(config('ca-crt.routes.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
