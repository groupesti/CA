<?php

declare(strict_types=1);

namespace CA\Ui;

use CA\Ui\Http\Middleware\CaUiAuthentication;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

final class UiServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ca-ui.php', 'ca-ui');
    }

    public function boot(): void
    {
        if (! config('ca-ui.enabled', true)) {
            return;
        }

        $this->registerMiddleware();
        $this->registerRoutes();
        $this->registerViews();
        $this->registerBladeComponents();
        $this->registerPublishing();
    }

    private function registerMiddleware(): void
    {
        /** @var Router $router */
        $router = $this->app->make(Router::class);
        $router->aliasMiddleware('ca-ui-auth', CaUiAuthentication::class);
    }

    private function registerRoutes(): void
    {
        Route::prefix(config('ca-ui.route_prefix', 'ca-admin'))
            ->middleware(config('ca-ui.middleware', ['web', 'auth']))
            ->group(__DIR__ . '/../routes/web.php');
    }

    private function registerViews(): void
    {
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'ca');
    }

    private function registerBladeComponents(): void
    {
        Blade::component('ca::components.stats-card', 'ca-stats-card');
        Blade::component('ca::components.status-badge', 'ca-status-badge');
        Blade::component('ca::components.certificate-chain', 'ca-certificate-chain');
        Blade::component('ca::components.dn-display', 'ca-dn-display');
        Blade::component('ca::components.pagination', 'ca-pagination');
    }

    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-ui.php' => config_path('ca-ui.php'),
            ], 'ca-ui-config');

            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/ca'),
            ], 'ca-ui-views');

            $this->publishes([
                __DIR__ . '/../public' => public_path('vendor/ca-ui'),
            ], 'ca-ui-assets');
        }
    }
}
