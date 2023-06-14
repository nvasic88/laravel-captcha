<?php

namespace nvasic88\LaravelCaptcha\Facades;

use Illuminate\Support\Facades\Facade;
use nvasic88\LaravelCaptcha\CaptchaManager;

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
