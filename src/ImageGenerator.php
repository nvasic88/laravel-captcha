<?php

namespace Nikazooz\LaravelCaptcha;

use Illuminate\Support\Facades\Config;

class ImageGenerator
{
    const LIBRARY_IMAGICK = 'imagick';
    const LIBRARY_GD = 'gd';

    /**
     * Default options for generating CAPTCHA image.
     *
     * @var array
     */
    protected $defaultOptions = [
        'width' => 240,
        'height' => 100,
        'padding' => 5,
        'background_color' => 0xFFFFFF,
        'text_color' => 0x2040A0,
        'offset' => -2,
    ];

    /**
     * Get config value.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function getConfig($key)
    {
        return Config::get("captcha.image.{$key}", $this->defaultOptions[$key]);
    }

    /**
     * Renders the CAPTCHA image.
     *
     * @param  string  $code The verification code.
     * @param  array  $options Configurations to generate image.
     * @return string  image Contents in PNG format.
     */
    public function render($code)
    {
        return $this->{$this->getRenderMethodName()}($code);
    }

    /**
     * Renders the CAPTCHA image based on the code using GD library.
     *
     * @param  string  $code The verification code
     * @return string Image contents in PNG format.
     */
    protected function renderByGd($code)
    {
        $width = $this->getConfig('width');
        $height = $this->getConfig('height');
        $backgroundColorConfig = $this->getConfig('background_color');
        $textColorConfig = $this->getConfig('text_color');
        $padding = $this->getConfig('padding');
        $offset = $this->getConfig('offset');

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
        $box = imagettfbbox(30, 0, $this->getFontFile(), $code);
        $w = $box[4] - $box[0] + $offset * ($length - 1);
        $h = $box[1] - $box[5];
        $scale = min(($width - $padding * 2) / $w, ($height - $padding * 2) / $h);
        $x = 10;
        $y = round($height * 27 / 40);

        for ($i = 0; $i < $length; ++$i) {
            $fontSize = (int) (mt_rand(26, 32) * $scale * 0.8);
            $angle = mt_rand(-10, 10);
            $letter = $code[$i];
            $box = imagettftext($image, $fontSize, $angle, $x, $y, $textColor, $this->getFontFile(), $letter);
            $x = $box[2] + $offset;
        }

        // Add lines for noise.
        for($i = 0; $i < 10; $i++) {
            imageline($image, 0, mt_rand() % $height, $width, mt_rand() % $height, $textColor);
        }

        // Add dots for noise.
        for($i = 0; $i < $width * $height * 0.4; $i++) {
            imagesetpixel($image, mt_rand() % $width, mt_rand() % $height, $textColor);
        }

        imagecolordeallocate($image, $textColor);

        ob_start();
        imagepng($image);
        imagedestroy($image);

        return ob_get_clean();
    }

    /**
     * Renders the CAPTCHA image based on the code using ImageMagick library.
     *
     * @param  string  $code The verification code
     * @return string Image contents in PNG format.
     */
    protected function renderByImagick($code)
    {
        $width = $this->getConfig('width');
        $height = $this->getConfig('height');
        $padding = $this->getConfig('padding');
        $offset = $this->getConfig('offset');

        $backgroundColor = new \ImagickPixel('#' . str_pad(
            dechex($this->getConfig('background_color')
        ), 6, 0, STR_PAD_LEFT));
        $textColor = new \ImagickPixel('#' . str_pad(
            dechex($this->getConfig('text_color')
        ), 6, 0, STR_PAD_LEFT));

        $image = new \Imagick();
        $image->newImage($width, $height, $backgroundColor);

        $draw = new \ImagickDraw();
        $draw->setFont($this->getFontFile());
        $draw->setFontSize(30);
        $fontMetrics = $image->queryFontMetrics($draw, $code);

        $length = strlen($code);
        $w = (int) $fontMetrics['textWidth'] - 8 + $offset * ($length - 1);
        $h = (int) $fontMetrics['textHeight'] - 8;
        $scale = min(($width - $padding * 2) / $w, ($height - $padding * 2) / $h);
        $x = 10;
        $y = round($height * 27 / 40);

        for ($i = 0; $i < $length; ++$i) {
            $draw = new \ImagickDraw();
            $draw->setFont($this->getFontFile());
            $draw->setFontSize((int) (mt_rand(26, 32) * $scale * 0.8));
            $draw->setFillColor($textColor);
            $image->annotateImage($draw, $x, $y, mt_rand(-10, 10), $code[$i]);
            $fontMetrics = $image->queryFontMetrics($draw, $code[$i]);
            $x += (int) $fontMetrics['textWidth'] + $offset;
        }

        $distorsion = new \ImagickDraw();
        $distorsion->setFillColor($textColor);
        $distorsion->setStrokeColor($textColor);

        // Add lines for noise.
        for($i = 0; $i < 10; $i++) {
            $distorsion->line(0, mt_rand() % $height, $width, mt_rand() % $height);
        }

        // Add dots for noise.
        for($i = 0; $i < $width * $height * 0.4; $i++) {
            $distorsion->point(mt_rand() % $width, mt_rand() % $height);
        }

        $image->drawImage($distorsion);

        $image->setImageFormat('png');

        return $image->getImageBlob();
    }

    /**
     * Get name of the method to render image.
     *
     * @return string
     */
    protected function getRenderMethodName()
    {
        return 'renderBy'.ucfirst($this->getLibraryName());
    }

    /**
     * Get names of all supported graphic libraries
     * that can be used to generate CAPTCHA image.
     *
     * @return array
     */
    protected function getSupportedImageLibraries()
    {
        return [
            static::LIBRARY_IMAGICK,
            static::LIBRARY_GD,
        ];
    }

    /**
     * Checks if there is graphic extension available to generate CAPTCHA images.
     *
     * @return string The name of the graphic extension.
     *
     * @throws \Exception If none of the supported libraries is available.
     */
    protected function getLibraryName()
    {
        $supported = $this->getSupportedImageLibraries();

        // Check if prefered library is available.
        $preferredImageLibrary = $this->getPreferredLibraryName();
        if ($this->isImageLibraryAvailable($preferredImageLibrary)) {
            return $preferredImageLibrary;
        }

        // Check if any of the supported libraries are available.
        foreach ($supported as $library) {
            if ($this->isImageLibraryAvailable($library)) {
                return $library;
            }
        }

        throw new \Exception('Either GD PHP extension with FreeType support or ImageMagick PHP extension with PNG support is required.');
    }

    /**
     * Name of the preferred image library.
     *
     * @return string
     */
    protected function getPreferredLibraryName()
    {
        return Config::get('captcha.preferred_image_library', static::LIBRARY_IMAGICK);
    }

    /**
     * Check if image library is available.
     *
     * @param  string  $library
     * @return bool
     */
    protected function isImageLibraryAvailable($library)
    {
        $checkMethodName = 'is'.ucfirst($library).'Available';

        if (! method_exists($this, $checkMethodName)) {
            return false;
        }

        return $this->{$checkMethodName}();
    }

    /**
     * Check if ImageMagick library is available.
     *
     * @return bool
     */
    protected function isImagickAvailable()
    {
        if (! extension_loaded(static::LIBRARY_IMAGICK)) {
            return false;
        }

        $imagickFormats = (new \Imagick())->queryFormats('PNG');

        return in_array('PNG', $imagickFormats, true);
    }

    /**
     * Check if GD library is aailable.
     *
     * @return bool
     */
    protected function isGdAvailable()
    {
        if (! extension_loaded(static::LIBRARY_GD)) {
            return false;
        }

        $gdInfo = gd_info();

        return ! empty($gdInfo['FreeType Support']);
    }


    /**
     * Returns font file path.
     *
     * @return string
     */
    protected function getFontFile()
    {
        return realpath(__DIR__.'/../resources/assets/fonts/SpicyRice.ttf');
    }

    /**
     * HTTP headers to be included when sending image.
     *
     * @return array
     */
    public function getHttpHeaders()
    {
        return [
            'Pragma' => 'public',
            'Expires' => '0',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Content-Transfer-Encoding' => 'binary',
            'Content-type' => 'image/png',
        ];
    }
}
