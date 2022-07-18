<?php

namespace SunAppModules\Core\src\FormBuilder\Fields;

use Closure;
use Exception;
use InvalidArgumentException;
use Traversable;

class Matrix extends ParentType
{
    /**
     * Contains template for a collection element.
     *
     * @var FormField
     */
    protected $proto;

    /**
     * @inheritdoc
     */
    protected $valueProperty = 'data';

    /**
     * @return string
     */
    protected function getTemplate()
    {
        return 'matrix';
    }

    /**
     * @inheritdoc
     */
    protected function getDefaults()
    {
        return [
            'type' => null,
            'options' => ['is_child' => true],
            'prototype' => true,
            'data-a' => null,
            'data-b' => null,
            'property' => 'id',
            'prototype_name' => '__NAME__',
            'empty_row' => true
        ];
    }

    /**
     * Get the prototype object.
     *
     * @return FormField
     * @throws Exception
     */
    public function prototype()
    {
        if ($this->getOption('prototype') === false) {
            throw new Exception(
                'Prototype for collection field [' . $this->name . '] is disabled.'
            );
        }

        return $this->proto;
    }

    /**
     * @inheritdoc
     */
    public function getAllAttributes()
    {
        // Collect all children's attributes.
        return $this->parent->getFormHelper()->mergeAttributes($this->children);
    }

    /**
     * @inheritdoc
     */
    protected function createChildren()
    {
        $this->children = [];
        $type = $this->getOption('type');
        $oldInput = $this->parent->getRequest()->old($this->getNameKey());
        $currentInput = $this->parent->getRequest()->input($this->getNameKey());

        try {
            $fieldType = $this->formHelper->getFieldType($type);
        } catch (Exception $e) {
            throw new Exception(
                'Collection field [' . $this->name . '] requires [type] option' . "\n\n" .
                $e->getMessage()
            );
        }

        $data_a = $this->getOption('data-axis-a', []);
        $data_b = $this->getOption('data-axis-b', []);

        if (!$data_a || empty($data_a) || !$data_b || empty($data_b)) {
            return $this->children = [];
        }

        if (!is_array($data_a) && !$data_a instanceof Traversable) {
            throw new Exception(
                'Data for collection field [' . $this->name . '] must be iterable.'
            );
        }

        $ka = $this->getOption('property-axis-a', 'id');
        $kb = $this->getOption('property-axis-b', 'id');
        $op = $this->getOption('options', []);
        $la = (isset($op['label-axis-a']) ? $op['label-axis-a'] : 'name');
        $lb = (isset($op['label-axis-b']) ? $op['label-axis-b'] : 'name');
        $data_a_k = [];
        foreach ($data_a as $key_a => $val_a) {
            if ($data_b instanceof Closure) {
                $data_bb = $data_b($val_a);
            } else {
                $data_bb = $data_b;
            }

            if (is_array($val_a)) {
                $k_a = $val_a[$ka];
            } else {
                $k_a = $val_a->$ka;
            }

            $data_a_k[$k_a] = ['label' => $val_a->$la];

            foreach ($data_bb as $dbb) {
                if (is_array($dbb)) {
                    $k_b = $dbb[$kb];
                } else {
                    if (isset($dbb->$kb)) {
                        $k_b = $dbb->$kb;
                    } else {
                        $k_b = false;
                    }
                }

                $value = 0;

                $column = $this->name;
                if ($this->parent->getModel()->$column) {
                    $data = $this->parent->getModel()->$column;
                    if (isset($data[$k_a]) && isset($data[$k_a][$k_b])) {
                        $value = $data[$k_a][$k_b];
                    }
                }

                $new_single_field = new $fieldType(
                    $this->name . '[' . $k_a . ']' . '[' . $k_b . ']',
                    $type,
                    $this->parent,
                    $this->getOption('options')
                );
                $new_single_field->setValue($value);
                $this->children[] = $new_single_field;
                if (isset($dbb->$kb)) {
                    $data_b_k[$dbb->$kb] = [
                        'label' => (isset($dbb->$lb) ? $dbb->$lb : ''),
                        'field' => $new_single_field
                    ];
                }
                if (isset($dbb->$kb)) {
                    $data_a_k[$k_a][$dbb->$kb] = $data_b_k[$dbb->$kb];
                }
            }
        }

        if (isset($data_a_k) && isset($data_b_k)) {
            $this->setOptions(['axis_a' => $data_a_k, 'axis_b' => $data_b_k]);
        }
    }

    /**
     * Set up a single child element for a collection.
     *
     * @param  FormField  $field
     * @param           $name
     * @param  null  $value
     * @return FormField
     */
    protected function setupChild($field, $name, $value = null)
    {
        $newFieldName = $field->getName() . $name;

        $firstFieldOptions = $this->formHelper->mergeOptions(
            $this->getOption('options'),
            ['attr' => ['id' => $newFieldName]]
        );

        $field->setName($newFieldName);
        $field->setOptions($firstFieldOptions);

        if ($value && !$field instanceof ChildFormType) {
            $value = $this->getModelValueAttribute(
                $value,
                $this->getOption('property')
            );
        }

        $field->setValue($value);

        return $field;
    }

    /**
     * Generate prototype for regular form field.
     *
     * @param  FormField  $field
     */
    protected function generatePrototype(FormField $field)
    {
        $value = $field instanceof ChildFormType ? false : null;
        $field->setOption('is_prototype', true);
        $field = $this->setupChild($field, $this->getPrototypeName(), $value);

        if ($field instanceof ChildFormType) {
            foreach ($field->getChildren() as $child) {
                if ($child instanceof CollectionType) {
                    $child->preparePrototype($child->prototype());
                }
            }
        }

        $this->proto = $field;
    }

    /**
     * Generate array like prototype name.
     *
     * @return string
     */
    protected function getPrototypeName()
    {
        return '[' . $this->getOption('prototype_name') . ']';
    }

    /**
     * Prepare collection for prototype by adding prototype as child.
     *
     * @param  FormField  $field
     */
    public function preparePrototype(FormField $field)
    {
        if (!$field->getOption('is_prototype')) {
            throw new InvalidArgumentException(
                'Field [' . $field->getRealName() . '] is not a valid prototype object.'
            );
        }

        $this->children = [];
        $this->children[] = $field;
    }

    public function setOption($name, $value)
    {
        array_set($this->options, $name, $value);
        //parent::setOption($name, $value);

        /*foreach ((array) $this->children as $key => $child) {
            $this->children[$key]->setOption($name, $value);
        }*/
    }
}
