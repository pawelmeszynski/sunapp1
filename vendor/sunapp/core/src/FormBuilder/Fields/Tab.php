<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

use Kris\LaravelFormBuilder\Form;

class Tab extends Group
{
    /**
     * @var Form
     */
    protected $form;

    public function __construct($name, $type, Form $parent, array $options = [])
    {
        parent::__construct($name, $type, $parent, $options);
        $this->label = $this->options['label'];
    }

    /**
     * @inheritdoc
     */
    protected function getTemplate()
    {
        return 'tabs';
    }

    public function render(array $options = [], $showLabel = true, $showField = true, $showError = true)
    {
        $options['children'] = $this->children;
        $fields = $this->parent->getfields();
        $this->prepareOptions($options);
        $value = $this->getValue();
        $defaultValue = $this->getDefaultValue();

        if ($showField) {
            $this->rendered = true;
        }

        // Override default value with value
        if (!$this->isValidValue($value) && $this->isValidValue($defaultValue)) {
            $this->setOption($this->valueProperty, $defaultValue);
        }

        if (!$this->needsLabel()) {
            $showLabel = false;
        }

        if ($showError) {
            $showError = $this->parent->haveErrorsEnabled();
        }

        $data = $this->getRenderData();

        return $this->formHelper->getView()->make(
            $this->getViewTemplate(),
            $data + [
                'name' => $this->name,
                'nameKey' => $this->getNameKey(),
                'type' => $this->type,
                'options' => $this->options,
                'active' => (array_search($this->name, array_keys($fields)) == 0 ? true : false),
                'showLabel' => $showLabel,
                'showField' => $showField,
                'showError' => $showError,
                'errorBag' => $this->parent->getErrorBag(),
                'translationTemplate' => $this->parent->getTranslationTemplate(),
            ]
        )->render();
    }

    public function active()
    {
        $fields = $this->parent->getfields();
        return array_search($this->name, array_keys($fields)) == 0 ? true : false;
    }
}
