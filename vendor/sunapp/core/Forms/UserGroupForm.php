<?php

namespace SunAppModules\Core\Forms;

use Illuminate\Validation\Rule;
use SunAppModules\Core\Entities\UserGroup;
use SunAppModules\Core\src\FormBuilder\Form;

class UserGroupForm extends Form
{
    public function buildForm()
    {
        $parents = UserGroup::defaultOrder()->where('id', '!=', $this->getModel()->id)->get();
        $parents_attributes = [];
        $parents_data = [];
        foreach ($parents as $parent) {
            $parents_attributes[$parent->id] = [$parent->name];
            $parents_data[$parent->id] = $parent->nested_name . ' (#' . $parent->id . ')';
        }
        $this->add('parent_id', 'select', [
            'label' => trans('core::groups.parent'),
            'property' => 'name',
            'attr' => ['data-url' => route('SunApp::core.groups.index')],
            'options_attr' => $parents_attributes,
            'choices' => $parents_data,
            'empty_value' => '&nbsp;'
        ]);
        $this->add('name', 'text', [
            'label' => trans('core::groups.name'),
            'rules' => [
                'required',
                Rule::unique('user_groups')->ignore($this->getModel()->id),
            ],
        ]);
        $this->add('description', 'textarea', [
            'label' => trans('core::groups.description'),
        ]);
    }
}
