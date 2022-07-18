<?php

namespace SunAppModules\Core\Http\Controllers;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Translation\FileLoader;
use SunApp\Http\Controllers\Controller as BaseController;
use SunAppModules\Core\Helpers\i18Next;
use Theme;

class I18NextController extends BaseController
{
    /**
     * @var i18next
     */
    private $i18next;
    /**
     * @var Filesystem
     */
    private $files;
    /**
     * @var FileLoader
     */
    private $translationLoader;

    /**
     * @param  i18next  $i18next
     */
    public function __construct(i18Next $i18next)
    {
        $this->i18next = $i18next;
        $this->files = app()['files'];
        $this->translationLoader = app()['translation.loader'];
    }

    /**
     * @param  string  $lang
     * @param  string  $namespace
     */
    public function fetch($lang, $namespace = '_default')
    {
        if ($namespace === '_default') {
            // Attempt to reference the Translation Strings as Key format
            $translations = $this->translationLoader->load($lang, '*', '*');
            foreach ($this->translationLoader->getPath() as $path) {
                if ($this->files->exists(sprintf('%s/%s/', $path, $lang))) {
                    $groups = $this->files->files(sprintf('%s/%s/', $path, $lang));
                    $excludeGroups = config('i18next.exclude.groups');
                    foreach ($groups as $group) {
                        $group = basename($group, '.php');
                        if (in_array($group, $excludeGroups)) {
                            continue;
                        }
                        $groupData = $this->translationLoader->load($lang, $group);
                        foreach ($groupData as $key => $val) {
                            $translations[$group][$key] = $val;
                        }
                    }
                }
            }
        } else {
            $translations = [];
            $namespaces = $this->translationLoader->namespaces();
            // Verify namespace exists
            if (!array_key_exists($namespace, $namespaces)) {
                return $translations;
            }
            // Now fetch all groups for the namespace
            $namespaces_themes[] = $namespace;
            if (
                isset($namespaces[Theme::info('parent') . '.' . $namespace])
                && Theme::info('parent') != ''
                && $this->files->exists(sprintf('%s/%s/', $namespaces[Theme::info('parent') . '.' . $namespace], $lang))
            ) {
                $namespaces_themes[] = Theme::info('parent') . '.' . $namespace;
            }
            if (
                isset($namespaces[Theme::info('name') . '.' . $namespace])
                && $this->files->exists(sprintf('%s/%s/', $namespaces[Theme::info('name') . '.' . $namespace], $lang))
            ) {
                $namespaces_themes[] = Theme::info('name') . '.' . $namespace;
            }
            foreach ($namespaces_themes as $namespace) {
                if ($this->files->exists(sprintf('%s/%s/', $namespaces[$namespace], $lang))) {
                    $groups = $this->files->files(sprintf('%s/%s/', $namespaces[$namespace], $lang));
                    $excludeGroups = config('i18next.exclude.groups');
                    // This is namespaced, let's fetch the namespace
                    foreach ($groups as $group) {
                        $group = basename($group, '.php');
                        if (in_array($group, $excludeGroups)) {
                            continue;
                        }
                        $groupData = $this->translationLoader->load($lang, $group, $namespace);
                        foreach ($groupData as $key => $val) {
                            $translations[$group][$key] = $val;
                            //$translations[$group . '.' . $key] = $val;
                        }
                    }
                }
            }
        }
        return $this->i18next->laravelToI18next($translations, config('i18next.flatten'));
    }
}
