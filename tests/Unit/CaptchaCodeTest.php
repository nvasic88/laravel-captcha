<?php

namespace nvasic88\LaravelCaptcha\Tests\Unit;

use nvasic88\LaravelCaptcha\CaptchaCode;
use PHPUnit\Framework\TestCase;

class CaptchaCodeTest extends TestCase
{
    protected $captchaCode;

    protected function setUp(): void
    {
        $this->captchaCode = new CaptchaCode();
    }

    /** @test */
    public function min_length_cannot_be_greater_than_max_length()
    {
        $code = $this->captchaCode->generate(4, 3);

        $this->assertEquals(4, strlen($code));
    }

    /** @test */
    public function code_length_is_between_min_and_max_length()
    {
        $code = $this->captchaCode->generate(4, 16);

        $this->assertGreaterThanOrEqual(4, strlen($code));
        $this->assertLessThanOrEqual(16, strlen($code));
    }
}
