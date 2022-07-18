<?php

namespace SunAppModules\Core\src\Theme;

use Config;
use Facuz\Theme\Breadcrumb;
use Facuz\Theme\Manifest;
use Facuz\Theme\Theme as BaseTheme;
use Facuz\Theme\UnknownLayoutFileException;
use Facuz\Theme\UnknownThemeException;
use Facuz\Theme\UnknownWidgetClassException;
use Illuminate\Config\Repository;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Events\Dispatcher;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Illuminate\View\Factory;
use ReflectionClass;

class Theme extends BaseTheme
{
    /**
     * The name of mail layout.
     *
     * @var string
     */
    protected $layout_mail;

    public function __construct(
        Repository $config,
        Dispatcher $events,
        Factory $view,
        Asset $asset,
        Filesystem $files,
        Breadcrumb $breadcrumb,
        Manifest $manifest
    ) {
        parent::__construct($config, $events, $view, $asset, $files, $breadcrumb, $manifest);
        $this->mailLayout($this->getConfig('layoutDefault'));
    }

    /**
     * Get all themes.
     *
     * @return Collection
     */
    public function all($withInfo = false)
    {
        $themes = [];

        if ($this->files->exists($this->getThemePath() . '../')) {
            $scannedThemes = $this->files->directories($this->getThemePath() . '../');
            foreach ($scannedThemes as $theme) {
                if ($withInfo) {
                    $themes[basename($theme)] = $this->info(null, null, basename($theme));
                    $themes[basename($theme)]['id'] = $themes[basename($theme)]['slug'];
                } else {
                    $themes[] = basename($theme);
                }
            }
        }
        return new Collection($themes);
    }

    public function allLayouts($theme = false)
    {
        if ($theme) {
            $theme_path = $this->path($theme) . '/';
        } else {
            $theme_path = $this->getThemePath();
        }
        $layouts = [];

        $theme_path = base_path($theme_path);
        if ($this->files->exists($theme_path . 'layouts/')) {
            $scannedLayouts = $this->files->glob($theme_path . 'layouts/*.php');
            foreach ($scannedLayouts as $layout) {
                $layouts[] = str_replace(['.php', '.blade'], '', basename($layout));
            }
        }
        return new Collection($layouts);
    }

    public function allViews($theme = false, $path = 'views')
    {
        if ($theme) {
            $theme_path = $this->path($theme) . '/';
        } else {
            $theme_path = $this->getThemePath();
        }
        $views = [];

        if ($this->files->exists(base_path($theme_path . $path . '/'))) {
            $scannedViews = $this->files->glob(base_path($theme_path . $path . '/*.php'));
            foreach ($scannedViews as $view) {
                $views[] = str_replace(['.php', '.blade'], '', basename($view));
            }
        }
        return new Collection($views);
    }

    public function getView()
    {
        return $this->view;
    }

    /**
     * Return a template with content.
     *
     * @param  int  $statusCode
     * @return Response
     * @throws UnknownLayoutFileException
     */
    public function render($statusCode = 200)
    {
        // Fire the event before render.
        $this->fire('after', $this);

        // Flush asset that need to serve.
        $this->asset->flush();

        $path = $this->getThemeNamespace('layouts.' . $this->layout);

        if (!$this->view->exists($path)) {
            throw new UnknownLayoutFileException("Layout [$this->layout] not found.");
        }

        $content = $this->view->make($path);

        // Append status code to view.
        /*$content = new Response($content, $statusCode);

        // Having cookie set.
        if ($this->cookie) {
            $content->withCookie($this->cookie);
        }*/

        return $content;
    }

    public function getFiles()
    {
        return $this->files;
    }

    /**
     * Get or set data on manifest.
     *
     * @return Collection
     */
    public function info($property = null, $value = null, $theme = false)
    {
        $this->checkTheme($theme);
        $info = $this->manifest;

        $info->setThemePath(base_path($this->path($theme)));

        if ($value && $property) {
            $info->setProperty($property, $value);
            return $value;
        }
        if ($property) {
            return $info->getProperty($property);
        }
        return $info->getJsonContents();
    }

    /**
     * Set up a theme name.
     *
     * @param  string  $theme
     * @return Theme
     * @throws UnknownThemeException
     */
    public function theme($theme = null)
    {
        $this->checkTheme($theme);
        $theme = parent::theme($theme);
        $mail_views_paths = config('mail.markdown.paths');
        $paths[] = base_path($theme->path()) . '/lang';
        if ($theme->getConfig('inherit')) {
            $inherit = $theme->getConfig('inherit');
            $paths[] = base_path($theme->path($inherit)) . '/lang';
            $mail_views_paths = array_merge([$theme->path($inherit) . '/views/notifications/mail'], $mail_views_paths);
            Config::set('mail.markdown.paths', $mail_views_paths);
        }
        $mail_views_paths = array_merge([$theme->getThemePath() . 'views/notifications/mail'], $mail_views_paths);
        Config::set('mail.markdown.paths', $mail_views_paths);
        $translator = app('translator');
        if (method_exists($translator, 'getLoaded')) {
            $loaded = $translator->getLoaded();
            if (isset($loaded['*'])) {
                unset($loaded['*']);
            }
            $translator->getLoader()->setPath(array_reverse($paths));
            $translator->setLoaded($loaded);
        }
        return $theme;
    }

    /**
     * Widget instance.
     *
     * @param  string  $className
     * @param  array  $attributes
     * @return Facuz\Theme\Widget
     * @throws UnknownWidgetClassException
     */
    public function widget($className, $attributes = [])
    {
        static $widgets = [];

        if (strpos($className, '::') !== false) {
            [$modelSlug, $className] = explode('::', $className);

            $modelName = ucfirst(Str::camel($modelSlug));

            if (!preg_match('|^[A-Z]|', $className)) {
                $className = ucfirst($className);
            }

            $widgetNamespace = 'SunAppModules\\' . $modelName . "\Widgets";
            $className = $widgetNamespace . '\\' . $className;
        } else {
            if (!preg_match('|^[A-Z]|', $className)) {
                $className = ucfirst($className);
            }
            // If the class name is not lead with upper case add prefix "Widget".
            $widgetNamespace = $this->getConfig('namespaces.widget');
            $className = $widgetNamespace . '\\' . $className;
        }
        $instance = array_get($widgets, $className);
        if (!$instance || $instance->flush) {
            $reflector = new ReflectionClass($className);

            if (!$reflector->isInstantiable()) {
                throw new UnknownWidgetClassException("Widget target [$className] is not instantiable.");
            }

            $instance = $reflector->newInstance($this, $this->config, $this->view);
            array_set($widgets, $className, $instance);
        }
        $instance->attributes = [];
        $instance->setAttributes($attributes);
        $instance->beginWidget();
        $instance->endWidget();

        return $instance;
    }

    public function getConfigInstance()
    {
        return $this->config;
    }

    public function setThemeConfig($themeConfig)
    {
        $this->themeConfig = $themeConfig;
        return $this;
    }

    /**
     * Set up a mail layout name.
     *
     * @param  string  $layout
     * @return Theme
     */
    public function mailLayout($layout)
    {
        // If layout name is not set, so use default from config.
        if ($layout != false) {
            $this->layout_mail = $layout;
        }

        return $this;
    }

    public function getMailLayout()
    {
        return $this->layout_mail;
    }

    public function setMailLayout()
    {
        $this->layout = $this->layout_mail;

        return $this;
    }

    private function checkTheme($theme)
    {
        if (($theme == 'default' || $theme == env('APP_THEME')) && !$this->exists($theme)) {
            \File::makeDirectory(base_path($this->path($theme)), 0775, true, true);
            \File::put(base_path($this->path($theme)) . '/theme.json', '{"slug": "default", "name": "Default"}');
            \File::put(base_path($this->path($theme)) . '/config.php', '<?php return [];');
        }
    }
}
