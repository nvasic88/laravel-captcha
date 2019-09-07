<?php

namespace Nikazooz\LaravelCaptcha\ImageGenerators;

use Illuminate\Contracts\Config\Repository;

abstract class BaseImageGenerator
{
    use GeneratesPngImage, ReadsImageConfig, UsesDefaultFont;

    public function __construct(Repository $config)
    {
        $this->config = $config;
    }
}
