<?php

namespace Nikazooz\LaravelCaptcha\ImageGenerators;

use Nikazooz\LaravelCaptcha\CaptchaManager;

trait ReadsImageConfig
{
    /**
     * Get config value.
     *
     * @param  string  $key
     * @return mixed
     */
    protected function config($key)
    {
        return $this->config->get(sprintf('%s.image.%s', CaptchaManager::CONFIG, $key));
    }
}
