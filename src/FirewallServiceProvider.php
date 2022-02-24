<?php

namespace WalkerChiu\Firewall;

use Illuminate\Support\ServiceProvider;

class FirewallServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        // Publish config files
        $this->publishes([
           __DIR__ .'/config/firewall.php' => config_path('wk-firewall.php'),
        ], 'config');

        // Publish migration files
        $from = __DIR__ .'/database/migrations/';
        $to   = database_path('migrations') .'/';
        $this->publishes([
            $from .'create_wk_firewall_table.php'
                => $to .date('Y_m_d_His', time()) .'_create_wk_firewall_table.php'
        ], 'migrations');

        $this->loadTranslationsFrom(__DIR__.'/translations', 'php-firewall');
        $this->publishes([
            __DIR__.'/translations' => resource_path('lang/vendor/php-firewall'),
        ]);

        if ($this->app->runningInConsole()) {
            $this->commands([
                config('wk-firewall.command.cleaner')
            ]);
        }

        config('wk-core.class.firewall.setting')::observe(config('wk-core.class.firewall.settingObserver'));
        config('wk-core.class.firewall.settingLang')::observe(config('wk-core.class.firewall.settingLangObserver'));
        config('wk-core.class.firewall.item')::observe(config('wk-core.class.firewall.itemObserver'));
    }

    /**
     * Register the blade directives
     *
     * @return void
     */
    private function bladeDirectives()
    {
    }

    /**
     * Merges user's and package's configs.
     *
     * @return void
     */
    private function mergeConfig()
    {
        if (!config()->has('wk-firewall')) {
            $this->mergeConfigFrom(
                __DIR__ .'/config/firewall.php', 'wk-firewall'
            );
        }

        $this->mergeConfigFrom(
            __DIR__ .'/config/firewall.php', 'firewall'
        );
    }

    /**
     * Merge the given configuration with the existing configuration.
     *
     * @param String  $path
     * @param String  $key
     * @return void
     */
    protected function mergeConfigFrom($path, $key)
    {
        if (
            !(
                $this->app instanceof CachesConfiguration
                && $this->app->configurationIsCached()
            )
        ) {
            $config = $this->app->make('config');
            $content = $config->get($key, []);

            $config->set($key, array_merge(
                require $path, $content
            ));
        }
    }
}
