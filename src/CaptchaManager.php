<?php

namespace Nikazooz\LaravelCaptcha;

use Illuminate\Http\Request;
use Illuminate\Support\Manager;
use Nikazooz\LaravelCaptcha\Http\Controllers\CaptchaController;
use Nikazooz\LaravelCaptcha\ImageGenerators\GdImageGenerator;
use Nikazooz\LaravelCaptcha\ImageGenerators\ImageGenerator;
use Nikazooz\LaravelCaptcha\ImageGenerators\ImagickImageGenerator;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class CaptchaManager extends Manager
{
    const CONFIG = 'captcha';
    const ROUTE_NAME = 'laravel-captcha.show';

    /**
     * Get the default driver name.
     *
     * @return string
     */
    public function getDefaultDriver()
    {
        return $this->config('driver');
    }

    /**
     * Create GD driver.
     *
     * @return ImageGenerator
     */
    public function createGdDriver()
    {
        $driver = new GdImageGenerator($this->config);

        if (! $driver->isAvailable()) {
            throw new \Exception('"gd" driver for Laravel Captcha requires GD extension for PHP to be installed');
        }

        return $driver;
    }

    /**
     * Create Imagick driver.
     *
     * @return ImageGenerator
     */
    public function createImagickDriver()
    {
        $driver = new ImagickImageGenerator($this->config);

        if (! $driver->isAvailable()) {
            throw new \Exception('"imagick" driver for Laravel Captcha requires Imagick extension for PHP to be installed');
        }

        return $driver;
    }

    /**
     * Get config value.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function config($key)
    {
        return $this->config->get(sprintf('%s.%s', self::CONFIG, $key));
    }

    /**
     * Return response with generated image.
     *
     * @param  Request  $request
     * @return BinaryFileResponse
     */
    public function respondTo(Request $request)
    {
        if ($request->has($this->config('refresh_query_param'))) {
            $this->code(true);

            return ['url' => $this->url()];
        }

        return response($this->render($this->code()), 200, $this->httpHeaders());
    }

    /**
     * Gets the verification code.
     *
     * @param  bool  $regenerate whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function code($regenerate = false)
    {
        $key = $this->sessionKey();

        if (! $this->session()->has($key) || $regenerate) {
            $this->session()->put($key, $this->generateCode());
            $this->session()->put($this->countSessionKey(), 1);
        }

        return $this->session()->get($key);
    }

    /**
     * Generates a new verification code.
     *
     * @return string The generated verification code
     */
    protected function generateCode()
    {
        return $this->container()->make(CaptchaCode::class)->generate(
            $this->config('min_length'),
            $this->config('max_length')
        );
    }

    /**
     * Session key.
     *
     * @return string
     */
    public function sessionKey()
    {
        return $this->config('session_key');
    }

    /**
     * Count session key.
     *
     * @return string
     */
    protected function countSessionKey()
    {
        return $this->sessionKey().'-count';
    }

    /**
     * Validate CAPTCHA.
     *
     * @param  string  $attribute
     * @param  string  $value
     * @return bool
     */
    public function validate($value)
    {
        $code = $this->code();

        $valid = $this->config('case_sensitive')
            ? ($value === $code)
            : strcasecmp($value, $code) === 0;

        $name = $this->countSessionKey();

        $this->session()->put($name, $this->session()->get($name) + 1);

        $testLimit = $this->config('allowed_failures');

        if ($valid || ($this->session()->get($name) > $testLimit && $testLimit > 0)) {
            $this->code(true);
        }

        return $valid;
    }

    protected function session()
    {
        return $this->container()->make('session');
    }

    /**
     * Get url of the CAPTCHA route.
     *
     * @return string
     */
    public function url()
    {
        return $this->container()->make('url')->route(static::ROUTE_NAME, ['v' => uniqid()]);
    }

    /**
     * Register route for retrieving CAPTCHA image.
     *
     * @return void
     */
    public function registerRoute()
    {
        $this->container()->make('router')->get(
            $this->config('route'),
            [CaptchaController::class, 'show']
        )->name(static::ROUTE_NAME)->middleware('web');
    }

    /**
     * Get the Container instance.
     *
     * We try to keep compatibility between Laravel 8 and previous versions.
     * Laravel 8 drops "app" property in favour of "container".
     * @see https://laravel.com/docs/8.x/upgrade
     *
     * @return \Illuminate\Contracts\Container\Container
     */
    protected function container()
    {
        return $this->container ?? $this->app;
    }
}
