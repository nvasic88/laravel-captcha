# Laravel Captcha

Package for Laravel to easily generate and validate CAPTCHA.

## Requirements

* Laravel 5.5
* ImageMagick or GD extension for PHP

**Note:** Verification code is stored in session, so the session needs to be active on validated route and on web middleware group.

## Installation

`composer require nikazooz/laravel-captcha`

## Configuration

To change configurations, you need to publish the configurations file.

`php artisan vendor:publish --provider="Nikazooz\LaravelCaptcha\CaptchaServiceProvider"`

Reading the config file is the best way to find out what can be configured.

## Usage

Easily get URL at witch CAPTCHA image is available using the facade:

```php
<?php

use Nikazooz\LaravelCaptcha\Facades\Captcha;

echo Captcha::url();

```

It adds `v` query parameter with random value in order to avoid browser caching.

To validate the code sent in request, use `captcha` validator, registered by the package, on that parameter.

If you need the verification code, for example in tests, you can get it with the facade: `Captcha::getVerificationCode();`

## License

The package is open-sourced software licensed under the [MIT license](http://opensource.org/licenses/MIT).
