<?php

namespace Nikazooz\LaravelCaptcha\Tests\Integration;

use Nikazooz\LaravelCaptcha\Facades\Captcha;
use Nikazooz\LaravelCaptcha\Tests\IntegrationTest;

class GenerateImageTest extends IntegrationTest
{
    /** @test */
    public function can_render_captcha_image_when_requested_using_gd()
    {
        $this->setConfig('driver', 'gd');

        if (! Captcha::isAvailable()) {
            $this->markTestSkipped(
              'The GD extension is not available.'
            );
        }

        $response = $this->withoutExceptionHandling()->get(Captcha::url());

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');

        $imageContent = $response->getContent();
        $this->assertNotEmpty($imageContent);

        list(, , $type) = getimagesizefromstring($imageContent);
        $expectedType = 3; // PNG
        $this->assertEquals($expectedType, $type);
    }

    /** @test */
    public function can_render_captcha_image_when_requested_using_imagick()
    {
        $this->setConfig('driver', 'imagick');

        if (! Captcha::isAvailable()) {
            $this->markTestSkipped(
              'The Imagick extension is not available.'
            );
        }

        $response = $this->withoutExceptionHandling()->get(Captcha::url());

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');

        $imageContent = $response->getContent();
        $this->assertNotEmpty($imageContent);

        list(, , $type) = getimagesizefromstring($imageContent);
        $expectedType = 3; // PNG
        $this->assertEquals($expectedType, $type);
    }

    /** @test */
    public function can_configure_image_width_and_height()
    {
        $this->setConfig('driver', 'gd');

        if (! Captcha::isAvailable()) {
            $this->markTestSkipped(
              'The GD extension is not available.'
            );
        }

        $this->setConfig('image.width', 500);
        $this->setConfig('image.height', 100);

        $response = $this->withoutExceptionHandling()->get(Captcha::url());

        $imageContent = $response->getContent();

        list($width, $height) = getimagesizefromstring($imageContent);
        $this->assertEquals(500, $width);
        $this->assertEquals(100, $height);
    }
}
