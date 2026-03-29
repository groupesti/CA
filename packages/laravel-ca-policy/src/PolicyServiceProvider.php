<?php

declare(strict_types=1);

namespace CA\Policy;

use CA\Policy\Console\Commands\IssuanceRuleListCommand;
use CA\Policy\Console\Commands\NameConstraintAddCommand;
use CA\Policy\Console\Commands\PolicyCreateCommand;
use CA\Policy\Console\Commands\PolicyEvaluateCommand;
use CA\Policy\Console\Commands\PolicyListCommand;
use CA\Policy\Contracts\NameConstraintInterface;
use CA\Policy\Contracts\PolicyEngineInterface;
use CA\Policy\Services\NameConstraintValidator;
use CA\Policy\Services\PolicyEngine;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\ServiceProvider;

class PolicyServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__ . '/../config/ca-policy.php', 'ca-policy');

        $this->app->singleton(NameConstraintInterface::class, NameConstraintValidator::class);

        $this->app->singleton(PolicyEngineInterface::class, PolicyEngine::class);
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/ca-policy.php' => config_path('ca-policy.php'),
            ], 'ca-policy-config');

            $this->publishes([
                __DIR__ . '/../database/migrations' => database_path('migrations'),
            ], 'ca-policy-migrations');

            $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

            $this->commands([
                PolicyCreateCommand::class,
                PolicyListCommand::class,
                PolicyEvaluateCommand::class,
                NameConstraintAddCommand::class,
                IssuanceRuleListCommand::class,
            ]);
        }

        $this->registerRoutes();
    }

    private function registerRoutes(): void
    {
        Route::prefix(config('ca-policy.routes.prefix', 'api/ca'))
            ->middleware(config('ca-policy.routes.middleware', ['api']))
            ->group(__DIR__ . '/../routes/api.php');
    }
}
