<?php

namespace Nikazooz\LaravelCaptcha\Facades;

use Illuminate\Support\Facades\Facade;
use Nikazooz\LaravelCaptcha\Captcha as LaravelCaptcha;

class Captcha extends Facade
{
    public static function getFacadeAccessor()
    {
        return LaravelCaptcha::class;
    }
}
