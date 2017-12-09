<?php

namespace Nikazooz\LaravelCaptcha;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Session;
use Nikazooz\LaravelCaptcha\VerificationCode;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Captcha
{
    const ROUTE_NAME = 'laravel-captcha.show';

    protected $defaultOptions = [
        'refresh_query_param' => 'refresh',
        'case_sensitive' => false,
        'allowed_failures' => 3,
        'min_length' => 6,
        'max_length' => 7,
        'route' => 'captcha',
        'session_key' => '__captcha',
    ];

    /**
     * @var ImageGenerator
     */
    protected $image;

    /**
     * @var VerificationCode
     */
    protected $verificationCode;

    /**
     * Constructor.
     *
     * @param ImageGenerator  $image
     * @param VerificationCode  $verificationCode
     */
    public function __construct(
        ImageGenerator $image,
        VerificationCode $verificationCode
    ) {
        $this->image = $image;
        $this->verificationCode = $verificationCode;
    }

    /**
     * Get config value.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getConfig($key) {
        return Config::get("captcha.{$key}", $this->defaultOptions[$key]);
    }

    /**
     * Return response with generated image.
     *
     * @param  Request  $request
     * @return BinaryFileResponse
     */
    public function respondTo(Request $request)
    {
        if ($request->has($this->getConfig('refresh_query_param'))) {
            $this->getVerificationCode(true);

            return ['url' => $this->url()];
        }

        $imageContents = $this->image->render($this->getVerificationCode());

        return response($imageContents, 200, $this->image->getHttpHeaders());
    }

    /**
     * Gets the verification code.
     *
     * @param  bool  $regenerate whether the verification code should be regenerated.
     * @return string the verification code.
     */
    public function getVerificationCode($regenerate = false)
    {
        $key = $this->getSessionKey();

        if (! Session::has($key) || $regenerate) {
            Session::put($key, $this->generateVerificationCode());
            Session::put($this->getCountSessionKey(), 1);
        }

        return Session::get($key);
    }

    /**
     * Generates a new verification code.
     *
     * @return string The generated verification code
     */
    protected function generateVerificationCode()
    {
        return $this->verificationCode->generate(
            $this->getConfig('min_length'),
            $this->getConfig('max_length')
        );
    }

    /**
     * Session key.
     *
     * @return string
     */
    public function getSessionKey()
    {
        return $this->getConfig('session_key');
    }

    /**
     * Count session key.
     *
     * @return string
     */
    protected function getCountSessionKey()
    {
        return $this->getSessionKey().'-count';
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
        $code = $this->getVerificationCode();

        $valid = $this->getConfig('case_sensitive')
            ? ($value === $code)
            : strcasecmp($value, $code) === 0;

        $name = $this->getCountSessionKey();

        Session::put($name, Session::get($name) + 1);

        $testLimit = $this->getConfig('allowed_failures');
        if ($valid || (Session::get($name) > $testLimit && $testLimit > 0)) {
            $this->getVerificationCode(true);
        }

        return $valid;
    }

    /**
     * Get url of the CAPTCHA route.
     *
     * @return string
     */
    public function url()
    {
        return URL::route(static::ROUTE_NAME, ['v' => uniqid()]);
    }

    /**
     * Register route for retrieving CAPTCHA image.
     *
     * @return void
     */
    public function registerRoute()
    {
        Route::get(
            $this->getConfig('route'),
            'Nikazooz\LaravelCaptcha\Http\Controllers\CaptchaController@show'
        )->name(static::ROUTE_NAME)->middleware('web');
    }
}
