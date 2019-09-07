<?php

namespace Nikazooz\LaravelCaptcha\Tests\Integration;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Nikazooz\LaravelCaptcha\Facades\Captcha;
use Nikazooz\LaravelCaptcha\Tests\IntegrationTest;

class CaptchaValidationTest extends IntegrationTest
{
    /** @test */
    public function can_validate_captcha()
    {
        $code = Captcha::code();

        $validator = Validator::make(['captcha' => $code], ['captcha' => 'captcha']);

        $this->assertNotEmpty($code);
        $this->assertTrue($validator->passes());
    }

    /** @test */
    public function validation_fails_if_invalid_code_is_provided()
    {
        $validator = Validator::make(['captcha' => 'invalid-code'], ['captcha' => 'captcha']);

        $this->assertTrue($validator->fails());
        $this->assertTrue($validator->errors()->has('captcha'));
    }

    /** @test */
    public function can_validate_captcha_with_case_sensitivity()
    {
        $this->setConfig('case_sensitive', true);

        $code = Captcha::code();

        $try = $this->startsWithUpper($code) ? strtolower($code) : strtoupper($code);

        $validator = Validator::make(['captcha' => $try], ['captcha' => 'captcha']);

        $this->assertTrue($validator->fails());
    }

    protected function startsWithUpper($str)
    {
        $chr = mb_substr ($str, 0, 1, "UTF-8");

        return mb_strtolower($chr, "UTF-8") != $chr;
    }

    /** @test */
    public function can_configure_allowed_number_of_failed_attempts_before_new_code_is_generated()
    {
        $allowedFailures = 2;
        $this->setConfig('allowed_failures', $allowedFailures);

        $startCode = Captcha::code();

        for ($i = 0; $i < $allowedFailures; $i++) {
            $this->assertEquals($startCode, Captcha::code());

            $validator = Validator::make(['captcha' => 'invalid-code'], ['captcha' => 'captcha']);

            $this->assertTrue($validator->fails());
        }

        $this->assertNotEquals($startCode, Captcha::code());
    }
}
