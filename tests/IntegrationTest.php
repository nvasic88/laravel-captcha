<?php

namespace Nikazooz\LaravelCaptcha\Tests;

use Orchestra\Testbench\TestCase;

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
        return ['Nikazooz\LaravelCaptcha\CaptchaServiceProvider'];
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
            'Captcha' => 'Nikazooz\LaravelCaptcha\Facades\Captcha',
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

    protected function setConfig($key, $value)
    {
        config()->set("captcha.{$key}", $value);
    }
}
