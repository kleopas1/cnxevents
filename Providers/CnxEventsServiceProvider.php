<?php

namespace Modules\CnxEvents\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

class CnxEventsServiceProvider extends ServiceProvider
{
    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

    /**
     * Boot the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerConfig();
        $this->registerViews();
        $this->registerFactories();
        $this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
        $this->hooks();
    }

    /**
     * Module hooks.
     */
    public function hooks()
    {
        // Add Events menu item to main navigation
        \Eventy::addAction('menu.append', function() {
            echo '<li class="' . \App\Misc\Helper::menuSelectedHtml('cnxevents') . '"><a href="' . route('cnxevents.events.index') . '"><i class="glyphicon glyphicon-calendar"></i> ' . __('Events') . '</a></li>';
        });

        // Add cnxevents to menu selection logic
        \Eventy::addFilter('menu.selected', function($menu) {
            $menu['cnxevents'] = [
                'cnxevents.events.index',
                'cnxevents.events.create',
                'cnxevents.events.show',
                'cnxevents.events.edit',
                'cnxevents.calendar',
                'cnxevents.analytics',
                'cnxevents.settings.index',
                'cnxevents.departments.index',
                'cnxevents.departments.create',
                'cnxevents.departments.show',
                'cnxevents.departments.edit',
                'cnxevents.custom-fields.index',
                'cnxevents.custom-fields.create',
                'cnxevents.custom-fields.show',
                'cnxevents.custom-fields.edit',
                'cnxevents.venues.index',
                'cnxevents.venues.create',
                'cnxevents.venues.show',
                'cnxevents.venues.edit',
                'cnxevents.beo.show',
            ];
            return $menu;
        });
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerTranslations();
    }

    /**
     * Register config.
     *
     * @return void
     */
    protected function registerConfig()
    {
        $this->publishes([
            __DIR__.'/../Config/config.php' => config_path('cnxevents.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'cnxevents'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/cnxevents');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/cnxevents';
        }, \Config::get('view.paths')), [$sourcePath]), 'cnxevents');
    }

    /**
     * Register translations.
     *
     * @return void
     */
    public function registerTranslations()
    {
        $this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
    }

    /**
     * Register an additional directory of factories.
     * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
     */
    public function registerFactories()
    {
        if (! app()->environment('production')) {
            app(Factory::class)->load(__DIR__ . '/../Database/factories');
        }
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [];
    }
}
