<?php

namespace Nikazooz\LaravelCaptcha\ImageGenerators;

class ImagickImageGenerator extends BaseImageGenerator implements ImageGenerator
{
    /**
     * Check if image generator can be used.
     *
     * @return bool
     */
    public function isAvailable(): bool
    {
        if (! class_exists('Imagick')) {
            return false;
        }

        $imagickFormats = (new \Imagick())->queryFormats('PNG');

        return in_array('PNG', $imagickFormats, true);
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
        $padding = $this->config('padding');
        $offset = $this->config('offset');

        $backgroundColor = new \ImagickPixel('#' . str_pad(
            dechex($this->config('background_color')
        ), 6, 0, STR_PAD_LEFT));
        $textColor = new \ImagickPixel('#' . str_pad(
            dechex($this->config('text_color')
        ), 6, 0, STR_PAD_LEFT));

        $image = new \Imagick();
        $image->newImage($width, $height, $backgroundColor);

        $draw = new \ImagickDraw();
        $draw->setFont($this->fontPath());
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = strlen($code);
        $w = (int) $fontMetrics['textWidth'] - 8 + $offset * ($length - 1);
        $h = (int) $fontMetrics['textHeight'] - 8;
        $scale = min(($width - $padding * 2) / $w, ($height - $padding * 2) / $h);
        $x = 10;
        $y = round($height * 27 / 40);

        $draw->setFillColor($textColor);

        for ($i = 0; $i < $length; ++$i) {
            $draw->setFontSize((int) (mt_rand(26, 32) * $scale * 0.8));
            $image->annotateImage($draw, $x, $y, mt_rand(-10, 10), $code[$i]);
            $fontMetrics = $image->queryFontMetrics($draw, $code[$i]);
            $x += (int) $fontMetrics['textWidth'] + $offset;
        }

        $draw->setStrokeColor($textColor);

        // Add lines for noise.
        for($i = 0; $i < 10; $i++) {
            $draw->line(0, mt_rand() % $height, $width, mt_rand() % $height);
        }

        // Add dots for noise.
        for($i = 0; $i < $width * $height * 0.4; $i++) {
            $draw->point(mt_rand() % $width, mt_rand() % $height);
        }

        $image->drawImage($draw);

        $image->setImageFormat('png');

        return $image->getImageBlob();
    }
}
