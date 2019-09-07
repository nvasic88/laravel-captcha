<?php

namespace Nikazooz\LaravelCaptcha;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Validator;

class CaptchaServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerRoute();

        $this->registerValidator();

        $this->offerPublishing();
    }

    /**
     * Register route from witch CAPTCHA image can be retrieved.
     *
     * @return void
     */
    protected function registerRoute()
    {
        $this->app->make(CaptchaManager::class)->registerRoute();
    }

    /**
     * Register CAPTCHA validator.
     *
     * @return void
     */
    protected function registerValidator()
    {
        Validator::extend('captcha', function ($attribute, $value) {
            return $this->app->make(CaptchaManager::class)->validate($value);
        });
    }

    /**
     * Setup the resource publishing groups.
     *
     * @return void
     */
    protected function offerPublishing()
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../config/captcha.php' => config_path('captcha.php'),
            ], 'laravel-captcha-config');
        }
    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $this->configure();

        $this->registerServices();
    }

    /**
     * Register class definitions.
     *
     * @return void
     */
    protected function registerServices()
    {
        $this->app->singleton(CaptchaCode::class);

        $this->app->singleton(CaptchaManager::class);
    }

    /**
     * Setup the configuration for the package.
     *
     * @return void
     */
    protected function configure()
    {
        $this->mergeConfigFrom(__DIR__.'/../config/captcha.php', CaptchaManager::CONFIG);
    }

    /**
     * Get the services provided by the provider.
     *
     * @return array
     */
    public function provides()
    {
        return [
            CaptchaCode::class,
            CaptchaManager::class,
        ];
    }
}
