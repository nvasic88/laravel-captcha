<?php

namespace Nikazooz\LaravelCaptcha\Facades;

use Illuminate\Support\Facades\Facade;
use Nikazooz\LaravelCaptcha\CaptchaManager;

class Captcha extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    public static function getFacadeAccessor()
    {
        return CaptchaManager::class;
    }
}
