<?php

namespace Nikazooz\LaravelCaptcha\ImageGenerators;

class GdImageGenerator extends BaseImageGenerator implements ImageGenerator
{
    /**
     * Check if image generator can be used.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (! function_exists('gd_info')) {
            return false;
        }

        $gdInfo = gd_info();

        return ! empty($gdInfo['FreeType Support']);
    }

    /**
     * Render CAPTCHA image with given code.
     *
     * @param  string  $code
     * @return string
     */
    public function render(string $code): string
    {
        $width = $this->config('width');
        $height = $this->config('height');
        $backgroundColorConfig = $this->config('background_color');
        $textColorConfig = $this->config('text_color');
        $padding = $this->config('padding');
        $offset = $this->config('offset');

        $image = imagecreatetruecolor($width, $height);

        $backgroundColor = imagecolorallocate(
            $image,
            (int) ($backgroundColorConfig % 0x1000000 / 0x10000),
            (int) ($backgroundColorConfig % 0x10000 / 0x100),
            $backgroundColorConfig % 0x100
        );
        imagefilledrectangle($image, 0, 0, $width - 1, $height - 1, $backgroundColor);
        imagecolordeallocate($image, $backgroundColor);

        $textColor = imagecolorallocate(
            $image,
            (int) ($textColorConfig % 0x1000000 / 0x10000),
            (int) ($textColorConfig % 0x10000 / 0x100),
            $textColorConfig % 0x100
        );

        $length = strlen($code);
        $box = imagettfbbox(30, 0, $this->fontPath(), $code);
        $w = $box[4] - $box[0] + $offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($width - $padding * 2) / $w, ($height - $padding * 2) / $h);
        $x = 10;
        $y = round($height * 27 / 40);

        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int) (mt_rand(26, 32) * $scale * 0.8);
            $angle = mt_rand(-10, 10);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $this->fontPath(), $letter);
            $x = $box[2] + $offset;
        }

        // Add lines for noise.
        for ($i = 0; $i < 10; $i++) {
            imageline($image, 0, mt_rand() % $height, $width, mt_rand() % $height, $textColor);
        }

        // Add dots for noise.
        for ($i = 0; $i < $width * $height * 0.4; $i++) {
            imagesetpixel($image, mt_rand() % $width, mt_rand() % $height, $textColor);
        }

        imagecolordeallocate($image, $textColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }
}
