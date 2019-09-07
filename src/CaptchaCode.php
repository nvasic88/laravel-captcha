<?php

namespace Nikazooz\LaravelCaptcha;

use Illuminate\Support\Str;

class CaptchaCode
{
    /**
     * Generates a CAPTCHA code.
     *
     * @param  int  $minLength
     * @param  int  $maxLength
     * @return string the generated verification code
     */
    public function generate($minLength = 6, $maxLength = 7)
    {
        if ($minLength > $maxLength) {
            $maxLength = $minLength;
        }

        if ($minLength < 3) {
            $minLength = 3;
        }

        if ($maxLength > 20) {
            $maxLength = 20;
        }

        return Str::random(mt_rand($minLength, $maxLength));
    }
}
