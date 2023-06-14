<?php

namespace nvasic88\LaravelCaptcha\ImageGenerators;

interface ImageGenerator
{
    /**
     * Check if image generator can be used.
     *
     * @return bool
     */
    public function isAvailable(): bool;

    /**
     * Render CAPTCHA image with given code.
     *
     * @param  string  $code
     * @return string
     */
    public function render(string $code): string;

    /**
     * Get HTTP headers for generated image.
     *
     * @return array
     */
    public function httpHeaders(): array;
}
