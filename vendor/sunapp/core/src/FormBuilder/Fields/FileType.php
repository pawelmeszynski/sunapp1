<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

use Kris\LaravelFormBuilder\Fields\InputType as BaseInputType;

class FileType extends BaseInputType
{
    use FormFieldTrait;

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'file';
    }

    /**
     * Default options for field.
     *
     * @return array
     */
    protected function getDefaults()
    {
        $rules = $this->parent->getConfig('defaults.' . $this->type . '.rules', [
            'file'
        ]);

        return [
            'rules' => implode('|', $rules)
        ];
    }
}
