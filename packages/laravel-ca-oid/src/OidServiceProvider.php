<?php

declare(strict_types=1);

namespace CA\Oid;

use CA\Oid\Console\Commands\OidListCommand;
use CA\Oid\Console\Commands\OidRegisterCommand;
use CA\Oid\Console\Commands\OidResolveCommand;
use CA\Oid\Console\Commands\OidSearchCommand;
use CA\Oid\Console\Commands\OidSeedCommand;
use CA\Oid\Contracts\OidRegistryInterface;
use CA\Oid\Contracts\OidResolverInterface;
use CA\Oid\Services\OidRegistry;
use CA\Oid\Services\OidResolver;
use CA\Oid\Services\OidValidator;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class OidServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ca-oid.php', 'ca-oid');

        $this->app->singleton(OidRegistry::class, function ($app) {
            return new OidRegistry(new OidValidator());
        });

        $this->app->singleton(OidValidator::class, function ($app) {
            return new OidValidator($app->make(OidRegistry::class));
        });

        $this->app->singleton(OidResolver::class, function ($app) {
            return new OidResolver($app->make(OidRegistry::class));
        });

        $this->app->singleton(OidRegistryInterface::class, function ($app) {
            return $app->make(OidRegistry::class);
        });

        $this->app->singleton(OidResolverInterface::class, function ($app) {
            return $app->make(OidResolver::class);
        });
    }

    public function boot(): void
    {
        $this->publishes([
            __DIR__ . '/../config/ca-oid.php' => config_path('ca-oid.php'),
        ], 'ca-oid-config');

        $this->publishes([
            __DIR__ . '/../database/migrations/' => database_path('migrations'),
        ], 'ca-oid-migrations');

        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        if ($this->app->runningInConsole()) {
            $this->commands([
                OidSearchCommand::class,
                OidRegisterCommand::class,
                OidListCommand::class,
                OidSeedCommand::class,
                OidResolveCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        Route::prefix(config('ca-oid.routes.prefix', 'api/ca'))
            ->middleware(config('ca-oid.routes.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
