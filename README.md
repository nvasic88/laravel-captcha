# Temporary cloned project

Project is updated clone of https://github.com/nikazooz/laravel-captcha to match Laravel 9 and 10 dependency for Biologer project update

# Laravel Captcha

Package for Laravel to easily generate and validate CAPTCHA.

## Requirements

| Version | PHP   | Laravel | Status        |
| ------- | ----- | ------- | ------------- |
| 0.1.*   | >=7.0 | ^5.5    | Not supported |
| 1.*     | >=7.2 | >=6.0   | Supported     |

ImageMagick or GD extension for PHP is required to use with included drivers.

> Verification code is stored in session, so session needs to be active on validated route and on web middleware group.

## Installation

```
composer require nikazooz/laravel-captcha
```

## Configuration

To change configurations, you need to publish the configurations file.

```
php artisan vendor:publish --provider="Nikazooz\LaravelCaptcha\CaptchaServiceProvider"
```

Reading the config file is the best way to find out what can be configured.

## Usage

Easily get URL at which CAPTCHA image is available using the facade:

```php
<?php

use Nikazooz\LaravelCaptcha\Facades\Captcha;

echo Captcha::url();

```

> It adds `v` query parameter with random value in order to avoid browser caching.

To validate the code sent in request, use `captcha` validator, registered by the package, on that parameter.

If you need the verification code, for example in tests, you can get it with the facade: `Captcha::code();`

## License

The package is open-sourced software licensed under the [MIT license](LICENSE.md).
