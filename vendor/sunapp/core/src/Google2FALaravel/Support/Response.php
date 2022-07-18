<?php

namespace SunAppModules\Core\src\Google2FALaravel\Support;

use PragmaRX\Google2FALaravel\Support\Response as BaseResponse;

trait Response
{
    use BaseResponse;

    /**
     * Get the OTP view.
     *
     * @return \Illuminate\Contracts\View\Factory|\Illuminate\View\View
     */
    private function getView()
    {
        return theme_view($this->config('view'));
    }
}
