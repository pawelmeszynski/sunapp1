<?php

namespace SunAppModules\Core\src\Translation;

use Illuminate\Translation\Translator as BaseTranslator;
use Theme;

class Translator extends BaseTranslator
{
    private function initThemes()
    {
        if (class_exists("\Theme")) {
            foreach (Theme::all() as $theme) {
                $this->addNamespace($theme, base_path(app('theme')->path($theme) . '/lang'));
            }
        }
    }

    public function get($key, array $replace = [], $locale = null, $fallback = true)
    {
//        $this->initThemes();

        $locale = $locale ?: $this->locale;

        // For JSON translations, there is only one file per locale, so we will simply load
        // that file and then we will be ready to check the array for the key. These are
        // only one level deep so we do not need to do any fancy searching through it.
        $this->load('*', '*', $locale);

        $line = $this->loaded['*']['*'][$locale][$key] ?? null;
        if (!isset($line)) {
            [$namespace, $group, $item] = $this->parseKey($key);
            if (class_exists("\Theme")) {
                if ($namespace != '*') {
                    $namespaces[] = Theme::info('slug') . '.' . $namespace;
                    if (Theme::getConfig('inherit')) {
                        $namespaces[] = Theme::getConfig('inherit') . '.' . $namespace;
                    }
                    if (!config('system.front')) {
                        $namespaces[] = $namespace;
                    }
                } else {
                    $namespaces[] = Theme::info('slug');
                    if (Theme::getConfig('inherit')) {
                        $namespaces[] = Theme::getConfig('inherit');
                    }
                    $namespaces[] = $namespace;
                }
            }

            // Here we will get the locale that should be used for the language line. If one
            // was not passed, we will use the default locales which was given to us when
            // the translator was instantiated. Then, we can load the lines and return.
            $locales = $fallback ? $this->localeArray($locale)
                : [$locale ?: $this->locale];
            foreach ($locales as $locale) {
                foreach ($namespaces as $namespace) {
                    if (
                        !is_null($line = $this->getLine(
                            $namespace,
                            $group,
                            $locale,
                            $item,
                            $replace
                        ))
                    ) {
                        break;
                    }
                }
                if (!is_null($line)) {
                    break;
                }
            }
        }
        // If the line doesn't exist, we will return back the key which was requested as
        // that will be quick to spot in the UI if language keys are wrong or missing
        // from the application's language files. Otherwise we can return the line.
        return $this->makeReplacements($line ?: $key, $replace);
    }

    /**
     * Get the loaded translation groups.
     *
     * @return array
     */
    public function getLoaded()
    {
        return $this->loaded;
    }
}
