<?php

namespace SunApp\Foundation\Translation;

use Illuminate\Translation\FileLoader as BaseFileLoader;

class FileLoader extends BaseFileLoader
{
    /**
     * Load a locale from a given path.
     *
     * @param string|array $path
     * @param string $locale
     * @param string $group
     * @return array
     */
    protected function loadPath($path, $locale, $group)
    {
        if (is_array($path)) {
            $messages = [];
            foreach ($path as $p) {
                if ($this->files->exists($full = "{$p}/{$locale}/{$group}.php")) {
                    $messages = array_merge($messages, $this->files->getRequire($full));
                }
            }
            return $messages;
        } else {
            if ($this->files->exists($full = "{$path}/{$locale}/{$group}.php")) {
                return $this->files->getRequire($full);
            }
        }
        return [];
    }

    public function getPath()
    {
        return $this->path;
    }

    public function setPath($path = [])
    {
        $this->path = $path;
    }


    /**
     * Load a namespaced translation group.
     *
     * @param string $locale
     * @param string $group
     * @param string $namespace
     * @return array
     */
    protected function loadNamespaced($locale, $group, $namespace)
    {
        if (isset($this->hints[$namespace])) {
            if (is_array($this->hints[$namespace])) {
                $files = $this->hints[$namespace];
                $lines = [];
                foreach ($files as $file) {
                    $lines = array_merge($lines, $this->loadPath($file, $locale, $group));
                }
            } else {
                $lines = $this->loadPath($this->hints[$namespace], $locale, $group);
            }
            return $this->loadNamespaceOverrides($lines, $locale, $group, $namespace);
        }
        return [];
    }

    /**
     * Load a locale from the given JSON file path.
     *
     * @param string $locale
     * @return array
     *
     * @throws \RuntimeException
     */
    protected function loadJsonPaths($locale)
    {
        return collect(array_merge($this->jsonPaths, [$this->path]))
            ->reduce(function ($output, $path) use ($locale) {
                if (is_array($path)) {
                    foreach ($path as $p) {
                        if ($this->files->exists($full = "{$p}/{$locale}.json")) {
                            $decoded = json_decode($this->files->get($full), true);

                            if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                                throw new RuntimeException(
                                    "Translation file [{$full}] contains an invalid JSON structure."
                                );
                            }

                            $output = array_merge($output, $decoded);
                        }
                    }
                } else {
                    if ($this->files->exists($full = "{$path}/{$locale}.json")) {
                        $decoded = json_decode($this->files->get($full), true);

                        if (is_null($decoded) || json_last_error() !== JSON_ERROR_NONE) {
                            throw new RuntimeException(
                                "Translation file [{$full}] contains an invalid JSON structure."
                            );
                        }

                        $output = array_merge($output, $decoded);
                    }
                }

                return $output;
            }, []);
    }

    /**
     * Load a local namespaced translation group for overrides.
     *
     * @param array $lines
     * @param string $locale
     * @param string $group
     * @param string $namespace
     * @return array
     */
    protected function loadNamespaceOverrides(array $lines, $locale, $group, $namespace)
    {
        if (is_array($this->path)) {
            foreach ($this->path as $p) {
                $file = "{$p}/vendor/{$namespace}/{$locale}/{$group}.php";

                if ($this->files->exists($file)) {
                    $lines = array_replace_recursive($lines, $this->files->getRequire($file));
                }
            }
        } else {
            $file = "{$this->path}/vendor/{$namespace}/{$locale}/{$group}.php";

            if ($this->files->exists($file)) {
                return array_replace_recursive($lines, $this->files->getRequire($file));
            }
        }

        return $lines;
    }
}
