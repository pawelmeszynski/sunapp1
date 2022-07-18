<?php

namespace SunAppModules\Core\src\Theme;

use Facuz\Theme\Widget as BaseWidget;

abstract class Widget extends BaseWidget
{
    public $flush = true;
    /**
     * Code to start this widget.
     */
    public function init(Theme $theme)
    {
        $this->checkNewView();
    }

    /**
     * Render widget to HTML.
     *
     * @return string
     * @throws UnknownWidgetFileException
     */
    public function render()
    {
        if ($this->enable == false) {
            return '';
        }

        if (strpos($this->template, '::') !== false) {
            [$module, $path] = explode('::', $this->template);

            $path = $this->theme->getThemeNamespace("modules/{$module}/widgets/{$path}");

            if (!$this->view->exists($path)) {
                $path = $this->template;
            }
        } else {
            $path = $this->theme->getThemeNamespace('widgets.' . $this->template);

            // If not found in theme widgets directory, try to watch in views/widgets again.
            if ($this->watch === true and !$this->view->exists($path)) {
                $path = 'widgets.' . $this->template;
            }
        }

        // Error file not exists.
        if (!$this->view->exists($path)) {
            throw new UnknownWidgetFileException("Widget view [$this->template] not found.");
        }

        $widget = $this->view->make($path, $this->data)->render();

        return $widget;
    }

    public function checkNewView()
    {
        if ($new_view = $this->getAttribute('view')) {
            if (strpos($new_view, '::') !== false) {
                [$module, $path] = explode('::', $new_view);

                $path = $this->theme->getThemeNamespace("modules/{$module}/widgets/{$path}");

                if (!$this->view->exists($path)) {
                    $path = $new_view;
                }
            } else {
                $path = $this->theme->getThemeNamespace('widgets.' . $new_view);

                // If not found in theme widgets directory, try to watch in views/widgets again.
                if ($this->watch === true and !$this->view->exists($path)) {
                    $path = 'widgets.' . $new_view;
                }
            }

            // Error file not exists.
            if (!$this->view->exists($path)) {
                throw new UnknownWidgetFileException("Widget view [$new_view] not found.");
            }
            $this->template = $new_view;
        }
    }
}
