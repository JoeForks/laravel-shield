<?php

/*
 * This file is part of Laravel Shield.
 *
 * (c) Vincent Klaiber <hello@vinkla.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Vinkla\Shield;

use Illuminate\Contracts\Container\Container;
use Illuminate\Foundation\Application as LaravelApplication;
use Illuminate\Support\ServiceProvider;
use Laravel\Lumen\Application as LumenApplication;
use Vinkla\Shield\Commands\HashCommand;

/**
 * This is the shield service provider class.
 *
 * @author Vincent Klaiber <hello@vinkla.com>
 */
class ShieldServiceProvider extends ServiceProvider
{
    /**
     * Boot the service provider.
     *
     * @return void
     */
    public function boot()
    {
        $this->setupConfig();

        $this->commands(['command.shield.hash']);
    }

    /**
     * Setup the config.
     *
     * @return void
     */
    protected function setupConfig()
    {
        $source = realpath(__DIR__.'/../config/shield.php');

        if ($this->app instanceof LaravelApplication && $this->app->runningInConsole()) {
            $this->publishes([$source => config_path('shield.php')]);
        } elseif ($this->app instanceof LumenApplication) {
            $this->app->configure('shield');
        }

        $this->mergeConfigFrom($source, 'shield');
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->registerHashCommand();
        $this->registerShield();
    }

    /**
     * Register the shield class.
     *
     * @return void
     */
    protected function registerShield()
    {
        $this->app->singleton('shield', function (Container $app) {
            $config = $app['config']['shield.users'];

            return new Shield($config);
        });

        $this->app->alias('shield', Shield::class);
    }

    /**
     * Register the hash command class.
     *
     * @return void
     */
    protected function registerHashCommand()
    {
        $this->app->singleton('command.shield.hash', function () {
            return new HashCommand();
        });
    }

    /**
     * Get the services provided by the provider.
     *
     * @return string[]
     */
    public function provides()
    {
        return [
            'shield',
            'command.shield.hash',
        ];
    }
}
