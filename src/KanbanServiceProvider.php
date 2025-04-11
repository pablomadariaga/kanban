<?php

namespace Pablomadariaga\Kanban;

use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

/**
 * Class KanbanServiceProvider
 *
 * Laravel service provider for the Pablomadariaga Kanban package.
 * It registers configuration, migrations, views, and Livewire components.
 */
class KanbanServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     * This method binds configuration settings.
     *
     * @return void
     */
    public function register(): void
    {
        // Merge the package configuration with the application's copy (if published).
        $this->mergeConfigFrom(
            __DIR__ . '/../config/kanban.php',
            'kanban'
        );
    }

    /**
     * Bootstrap any application services.
     * This method publishes assets, registers Livewire components, and loads views/migrations.
     *
     * @return void
     */
    public function boot(): void
    {
        // Publish configuration file to the application's config directory.
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../config/kanban.php' => config_path('kanban.php'),
            ], 'kanban-config');

            // Publish migrations to the application's database/migrations directory.
            $this->publishes([
                __DIR__ . '/../database/migrations/' => database_path('migrations'),
            ], 'kanban-migrations');

            // Publish the CSS asset to the public directory.
            $this->publishes([
                __DIR__ . '/../public/css/kanban.css' => public_path('vendor/pablomadariaga/kanban/css/kanban.css'),
            ], 'kanban-assets');

            // Publish views to allow customization by the application.
            $this->publishes([
                __DIR__ . '/../resources/views/' => resource_path('views/vendor/kanban'),
            ], 'kanban-views');
        }

        // Automatically load the package's migrations without requiring publishing.
        $this->loadMigrationsFrom(__DIR__ . '/../database/migrations');

        // Load the package's views and assign a view namespace ("kanban").
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'kanban');

        // Register the Livewire component for the Kanban board, if Livewire is available.
        if (class_exists(Livewire::class)) {
            Livewire::component('kanban-board', \Pablomadariaga\Kanban\Livewire\KanbanBoard::class);
        }
    }
}
