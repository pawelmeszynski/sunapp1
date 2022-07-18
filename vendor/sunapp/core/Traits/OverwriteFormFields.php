<?php

namespace SunAppModules\Core\Traits;

trait OverwriteFormFields
{
    public function overwriteNameField()
    {
        if (request()->post() && request()->has('name')) {
            $names = request()->get('name');
            $defaultValue = '';
            foreach ($names as $trans) {
                if ($trans) {
                    $defaultValue = $trans;
                    break;
                }
            }
            if ($defaultValue != '') {
                foreach ($names as $langCode => $trans) {
                    if (!$trans) {
                        $names[$langCode] = $defaultValue;
                    }
                }
                request()->merge(['name' => $names]);
            }
        }
    }
}
