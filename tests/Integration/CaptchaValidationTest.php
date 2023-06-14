<?php

namespace nvasic88\LaravelCaptcha\Tests\Integration;

use Illuminate\Support\Facades\Validator;
use nvasic88\LaravelCaptcha\Facades\Captcha;
use nvasic88\LaravelCaptcha\Tests\IntegrationTest;

class CaptchaValidationTest extends IntegrationTest
{
    /** @test */
    public function can_validate_captcha()
    {
        $code = Captcha::code();

        $validator = Validator::make(['captcha' => $code], ['captcha' => 'captcha']);

        $this->assertNotEmpty($code);
        $this->assertFalse($validator->fails());
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

        $try = $this->containsUppercase($code) ? strtolower($code) : strtoupper($code);

        $validator = Validator::make(['captcha' => $try], ['captcha' => 'captcha']);

        $this->assertTrue($validator->fails());
    }

    protected function containsUppercase($str)
    {
        return preg_match('/[A-Z]/', $str);
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
