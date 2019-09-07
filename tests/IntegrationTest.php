<?php

namespace Nikazooz\LaravelCaptcha\Tests;

use Orchestra\Testbench\TestCase;
use Nikazooz\LaravelCaptcha\Facades\Captcha;
use Nikazooz\LaravelCaptcha\CaptchaServiceProvider;

abstract class IntegrationTest extends TestCase
{
    /**
     * Define package service providers.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageProviders($app)
    {
        return [CaptchaServiceProvider::class];
    }

    /**
     * Define package aliases.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return array
     */
    protected function getPackageAliases($app)
    {
        return [
            'Captcha' => Captcha::class,
        ];
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        $app['config']->set('app.key', 'base64:UTyp33UhGolgzCK5CJmT+hNHcA+dJyp3+oINtX+VoPI=');
    }

    /**
     * Set Laravel Captcha config.
     *
     * @param  string  $key
     * @param  mixed  $value
     * @return void
     */
    protected function setConfig(string $key, $value)
    {
        config()->set("captcha.{$key}", $value);
    }
}
