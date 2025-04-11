<?php

namespace Pablomadariaga\Kanban;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;
use Pablomadariaga\Kanban\Foundation\Support\Blade\Directives;

/**
 * Class KanbanServiceProvider
 *
 * Laravel service provider for the Pablomadariaga Kanban package.
 * It registers configuration, migrations, routes, views, translations,
 * Livewire components and Blade directives.
 */
class KanbanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * This method binds configuration settings and registers the Kanban singleton.
     *
     * @return void
     */
    public function register(): void
    {
        $this->registerConfig();

        // Register the singleton for the Kanban facade.
        $this->app->singleton('Kanban', Kanban::class);
    }

    /**
     * Bootstrap any application services.
     * This method publishes assets (when running in console), registers Livewire components,
     * and registers custom Blade directives.
     *
     * @return void
     */
    public function boot(): void
    {
        // Register resource publishing only when running in console.
        if ($this->app->runningInConsole()) {
            $this->publishes([
                // Publish configuration file to the application's config directory.
                __DIR__ . '/../config/kanban.php' => config_path('kanban.php'),
            ], 'kanban-config');

            $this->publishes([
                // Publish migrations to the application's database/migrations directory.
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'kanban-migrations');

            $this->publishes([
                // Publish the CSS build asset to the public directory.
                __DIR__ . '/../public/css/kanban.css' => public_path('vendor/pablomadariaga/kanban/css/kanban.css'),
            ], 'kanban-assets');

            $this->publishes([
                // Publish views to allow customization by the application.
                __DIR__ . '/../resources/views/' => resource_path('views/vendor/kanban'),
            ], 'kanban-views');
        }

        // Register custom Blade directives so they are always available.
        Directives::register();

        // Register the Livewire component for the Kanban board, if Livewire is available.
        if (class_exists(Livewire::class)) {
            Livewire::component('kanban-board', \Pablomadariaga\Kanban\Livewire\KanbanBoard::class);
        }
    }

    /**
     * Register package configuration, migrations, views, routes, and translations.
     *
     * @return void
     */
    protected function registerConfig(): void
    {
        // Load the package's migrations so they run without publishing.
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load the package's views and assign a namespace ("kanban").
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'kanban');

        // Merge the package configuration with the application's copy (if published).
        $this->mergeConfigFrom(__DIR__ . '/../config/kanban.php', 'kanban');

        // Load the package's routes.
        $this->loadRoutesFrom(__DIR__ . '/routes.php');

        // Load the package's translations.
        $this->loadTranslationsFrom(__DIR__ . '/lang', 'kanban');
    }
}
