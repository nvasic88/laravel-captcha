<?php

namespace nvasic88\LaravelCaptcha\ImageGenerators;

trait UsesDefaultFont
{
    /**
     * Returns font file path.
     *
     * @return string
     */
    protected function fontPath()
    {
        return realpath(__DIR__.'/../../resources/fonts/SpicyRice.ttf');
    }
}
