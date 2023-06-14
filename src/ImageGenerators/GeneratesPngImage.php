<?php

namespace nvasic88\LaravelCaptcha\ImageGenerators;

trait GeneratesPngImage
{
    /**
     * Get HTTP headers for generated image.
     *
     * @return array
     */
    public function httpHeaders(): array
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
