<?php

namespace SunAppModules\Core\src\Mail;

use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Mail\Mailer as BaseMailer;

class Mailer extends BaseMailer
{
    /**
     * Render the given view.
     *
     * @param  string  $view
     * @param  array  $data
     * @return string
     */
    protected function renderView($view, $data)
    {
        if (\Theme::getMailLayout()) {
            \Theme::setMailLayout();
            return $view instanceof Htmlable
                ? $view->toHtml()
                : theme_view($view, $data)->render();
        }
        return parent::renderView($view, $data);
    }
}
