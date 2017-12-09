<?php

namespace Nikazooz\LaravelCaptcha\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller;
use Nikazooz\LaravelCaptcha\Facades\Captcha;

class CaptchaController extends Controller
{
    /**
     * Return captha image for user to used for form verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Symfony\Component\HttpFoundation\BinaryFileResponse
     */
    public function show(Request $request)
    {
        return Captcha::respondTo($request);
    }
}
