<?php

use SunAppModules\Core\src\FormBuilder\Form;
use Illuminate\Support\Facades\Cache;

if (!function_exists('ip_in_range')) {
    function ip_in_range($ip, $range)
    {
        if (strpos($range, '/') !== false) { // a.b.c.d/netmask format
            list($range, $netmask) = explode('/', $range, 2);
            if (strpos($netmask, '.') !== false) {
                $netmask = str_replace('*', '0', $netmask);
                $netmask_dec = ip2long($netmask);
                return ( (ip2long($ip) & $netmask_dec) == (ip2long($range) & $netmask_dec) );
            } else {
                $x = explode('.', $range);
                while (count($x) < 4) {
                    $x[] = '0';
                }
                list($a,$b,$c,$d) = $x;
                $range = sprintf(
                    "%u.%u.%u.%u",
                    empty($a) ? '0' : $a,
                    empty($b) ? '0' : $b,
                    empty($c) ? '0' : $c,
                    empty($d) ? '0' : $d
                );
                $range_dec = ip2long($range);
                $ip_dec = ip2long($ip);
                $wildcard_dec = pow(2, (32 - $netmask)) - 1;
                $netmask_dec = ~ $wildcard_dec;
                return (($ip_dec & $netmask_dec) == ($range_dec & $netmask_dec));
            }
        } else {
            if (strpos($range, '*') !== false) { // a.b.*.* format
                $lower = str_replace('*', '0', $range);
                $upper = str_replace('*', '255', $range);
                $range = "$lower-$upper";
            }
            if (strpos($range, '-') !== false) { // a.b.c.d-a.b.c.d format
                list($lower, $upper) = explode('-', $range, 2);
                $lower_dec = (float)sprintf("%u", ip2long($lower));
                $upper_dec = (float)sprintf("%u", ip2long($upper));
                $ip_dec = (float)sprintf("%u", ip2long($ip));
                return ( ($ip_dec >= $lower_dec) && ($ip_dec <= $upper_dec) );
            }
            if ($range == $ip) {
                return true;
            }
            return false;
        }
    }
}

if (!function_exists('theme_view')) {
    function theme_view($view = null, $data = [], $mergeData = [])
    {
        return Theme::view($view, $data, $mergeData);
    }
}

if (!function_exists('form_field')) {
    function form_field(Form $form, $field_name)
    {
        return $form->renderField($field_name, false);
    }
}

if (!function_exists('form_manual')) {
    function form_manual(Form $form, $field_name)
    {
        $field = $form->getField($field_name);
        $field->setRendered();
        return '';
    }
}

if (!function_exists('form_group')) {
    function form_group(Form $form, $field_name)
    {
        return $form->renderField($field_name . '-group', false);
    }
}

if (!function_exists('form_fieldset')) {
    function form_fieldset(Form $form, $field_name)
    {
        return $form->renderField($field_name . '-fieldset', false);
    }
}

if (!function_exists('form_tab')) {
    function form_tab(Form $form, $field_name)
    {
        return $form->renderField($field_name . '-tab', false);
    }
}

if (!function_exists('form_tab_fields')) {
    function form_tab_fields(Form $form, $tab, array $options = [])
    {
        $tab = $tab . '-tab';
        $fields = $form->getField($tab)->getChildren();

        return $fields;
    }
}

if (!function_exists('form_langs')) {
    function form_langs(Form $form)
    {
        return $form->getLangs();
    }
}

if (!function_exists('form_lang_selector')) {
    function form_lang_selector(Form $form, array $options = [])
    {
        return $form->getLangSelector($options);
    }
}

if (!function_exists('theme_asset')) {
    function theme_asset($name = '', $path = '', $after = [], $internal = true)
    {
        app('theme')->asset()->themePath($internal)->add($name, $path, $after);
    }
}

if (!function_exists('sunapp_version')) {
    function sunapp_version()
    {
        if (!Cache::has('sunapp_version')) {
            $composerPath = base_path() . '/composer.lock';
            $composer = file_get_contents($composerPath);
            $composer = json_decode($composer, true);
            $packages = collect($composer['packages']);
            $sunapp = $packages->where('name', 'sungroup/sunapp')->first();
            Cache::put('sunapp_version', $sunapp['version'], now()->addDays(1));
        }
        return Cache::get('sunapp_version');
    }
}

if (!function_exists('process_queues')) {
    function process_queues()
    {
        if (config('queue.default') != 'sync') {
            if (Schema::hasTable('jobs')) {
                return DB::table('jobs')->count();
            }
        }
        return 0;
    }
}
