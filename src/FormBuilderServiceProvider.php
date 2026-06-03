<?php

namespace Hyderkamran\FormBuilder;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;

class FormBuilderServiceProvider extends ServiceProvider
{
    /**
     * Register package services.
     */
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../config/form-builder.php', 'form-builder');

        $this->app->singleton('form-builder', fn ($app) => new FormBuilder());
    }

    /**
     * Bootstrap package services.
     */
    public function boot(): void
    {
        // ── Publishable assets ──────────────────────────────────────
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/form-builder.php' => config_path('form-builder.php'),
            ], 'form-builder-config');

            $this->publishes([
                __DIR__.'/../database/migrations' => database_path('migrations'),
            ], 'form-builder-migrations');

            $this->publishes([
                __DIR__.'/../database/seeders' => database_path('seeders'),
            ], 'form-builder-seeders');

            $this->publishes([
                __DIR__.'/../resources/views' => resource_path('views/vendor/form-builder'),
            ], 'form-builder-views');

            $this->publishes([
                __DIR__.'/../resources/js' => public_path('vendor/form-builder'),
            ], 'form-builder-assets');
        }

        // ── Auto-load ───────────────────────────────────────────────
        $this->loadMigrationsFrom(__DIR__.'/../database/migrations');
        $this->loadRoutesFrom(__DIR__.'/../routes/web.php');
        $this->loadRoutesFrom(__DIR__.'/../routes/api.php');
        $this->loadViewsFrom(__DIR__.'/../resources/views', 'form-builder');

        // ── Blade Directives ────────────────────────────────────────
        Blade::directive('form', function (string $expression): string {
            return "<?php echo \\Hyderkamran\\FormBuilder\\Facades\\FormBuilder::render({$expression}); ?>";
        });
    }
}
