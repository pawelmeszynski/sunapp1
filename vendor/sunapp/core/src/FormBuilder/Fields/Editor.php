<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

class Editor extends TextareaType
{
    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'editor';
    }
}
