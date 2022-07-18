<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

use Kris\LaravelFormBuilder\Fields\InputType as BaseInputType;

class ImageType extends BaseInputType
{
    use FormFieldTrait;

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'image';
    }

    /**
     * Default options for field.
     *
     * @return array
     */
    protected function getDefaults()
    {
        $rules = $this->parent->getConfig('defaults.' . $this->type . '.rules', [
            'image'
            //'mimes:jpeg,png,bmp,gif',
            //'dimensions:min_width=100,min_height=100,max_width=4000,max_height=4000'
        ]);

        return [
            'rules' => implode('|', $rules)
        ];
    }
}
