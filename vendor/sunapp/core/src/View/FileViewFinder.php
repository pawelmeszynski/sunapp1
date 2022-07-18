<?php

namespace SunAppModules\Core\src\View;

use Exception;
use Illuminate\View\FileViewFinder as BaseFileViewFinder;
use Str;
use Theme;

class FileViewFinder extends BaseFileViewFinder
{
    /**
     * Get the fully qualified location of the view.
     *
     * @param  string  $name
     * @return string
     */
    public function find($name)
    {
        if (isset($this->views[$name])) {
            return $this->views[$name];
        }

        if ($this->hasHintInformation($name = trim($name))) {
            try {
                return $this->views[$name] = $this->findNamespacedView($name);
            } catch (Exception $e) {
                if (Str::contains($name, '::') && !Str::contains($name, 'theme.')) {
                    $segments = explode('::', $name);
                    $theme = 'theme.' . Theme::getThemeName() . '::';
                    if ($segments[0] == 'errors') {
                        $name = $theme . "views.{$segments[0]}.{$segments[1]}";
                    } else {
                        $name = $theme . "modules.{$segments[0]}.views.{$segments[1]}";
                    }
                }
                return $this->views[$name] = $this->findNamespacedView($name);
            }
        }
        return $this->views[$name] = $this->findInPaths($name, $this->paths);
    }

    protected function initTheme($theme = null)
    {
        $theme = app('theme');
        // Add Theme Paths to FileViewFinder
        $this->addThemePathLocation($theme->path(), $theme);
        $view = $theme->getView();
        $global_view = app('view');
        $finder = $global_view->getFinder();

        if ($theme->getConfig('inherit')) {
            // Inherit from theme name.
            $inherit = $theme->getConfig('inherit');
            $finder->prependNamespace('errors', $theme->path($inherit) . '/views/errors');
            $finder->addLocation($theme->path($inherit) . '/views');
        }

        $finder->prependNamespace('errors', $theme->getThemePath() . 'views/errors');
        $finder->addLocation($theme->getThemePath() . 'views');

        $view->setFinder($finder);

        // Fire event before set up a theme.
        $theme->fire('before', $theme);
        // Before from a public theme config.
        $theme->fire('appendBefore', $theme);

        if ($theme->getConfig('inherit_events')) {
            $clone_theme = clone app('theme');
            $parent_theme = $clone_theme->theme($theme->getConfig('inherit'));
            $parent_theme->fire('before', $theme);
            $parent_theme->fire('appendBefore', $theme);
        }

        // Add asset path to asset container.
        $theme->asset()->addPath($theme->path() . '/assets');
        if ($theme->getConfig('inherit_assets')) {
            $theme->asset()->addPath($theme->path($theme->getConfig('inherit')) . '/assets');
        }

        /* foreach($theme->all() as $them) {
             $theme->addNamespace($them, $theme->path($them)."/view");
         }*/

        return $theme;
    }

    protected function getThemeNamespace($view, $theme = null)
    {
        if ($theme) {
            if ($this->hasHintInformation($name = trim($view))) {
                $segments = explode('::', $name);
                if ($segments[0] == 'errors') {
                    $view = $theme->getThemeNamespace("views.{$segments[0]}.{$segments[1]}");
                } else {
                    $view = $theme->getThemeNamespace("modules.{$segments[0]}.views.{$segments[1]}");
                }
            } else {
                $view = $theme->getThemeNamespace('views.' . $view);
            }
        }

        return $view;
    }

    /**
     * Add location path to look up.
     *
     * @param  string  $location
     */
    protected function addThemePathLocation($location, $theme)
    {
        // First path is in the selected theme.
        $hints[] = base_path($location);
        // This is nice feature to use inherit from another.
        if ($theme->getConfig('inherit')) {
            // Inherit from theme name.
            $inherit = $theme->getConfig('inherit');

            // Inherit theme path.
            $inheritPath = base_path($theme->path($inherit));

            if ($theme->getFiles()->isDirectory($inheritPath)) {
                array_push($hints, $inheritPath);
            }
        }
        // Add namespace with hinting paths.
        $this->addNamespace($theme->getThemeNamespace(), $hints);
    }
}
