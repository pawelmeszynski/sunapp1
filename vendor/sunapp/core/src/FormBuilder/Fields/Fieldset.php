<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

use Kris\LaravelFormBuilder\Form;

class Fieldset extends Group
{
    /**
     * @var Form
     */
    protected $form;

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'fieldset';
    }

    /**
     * {inheritdoc}
     */
    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true)
    {
        $options['children'] = $this->children;
        $options['wrapper'] = array_merge(
            ['class' => $this->getConfig('defaults.fieldset_class')],
            (isset($options['wrapper']) ? $options['wrapper'] : [])
        );
        return parent::render($options, $showLabel, $showField, $showError);
    }
}
