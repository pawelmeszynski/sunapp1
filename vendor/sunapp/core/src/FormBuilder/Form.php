<?php

namespace SunAppModules\Core\src\FormBuilder;

use App;
use Exception;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Str;
use Kris\LaravelFormBuilder\Events\BeforeFormValidation;
use Kris\LaravelFormBuilder\Form as BaseFrom;
use Request;
use Route;
use SunAppModules\Core\src\FormBuilder\Fields\FormField;

class Form extends BaseFrom
{
    /**
     * Form options.
     *
     * @var array
     */
    protected $formOptions = [
        'method' => 'POST',
        'url' => null
    ];

    /**
     * All fields that are added.
     *
     * @var array
     */
    protected $all_fields = [];
    protected $parent_form = false;

    public function getParentForm()
    {
        return $this->parent_form;
    }

    public function setParentForm($parent)
    {
        $this->parent_form = $parent;
        return $this;
    }

    public function addTabs($name, $function, array $options = [], $modify = false)
    {
        $model = $this->getModel();
        $form = $this->formBuilder->plain(['model' => $model]);
        $form->setParentForm($this);
        $function($form);
        return $this->add($name . '-tabs', 'tab', array_merge(['class' => $form, 'label' => false], $options));
    }

    public function addTab($name, $function, array $options = [], $modify = false)
    {
        $model = $this->getModel();
        $form = $this->formBuilder->plain(['model' => $model]);
        $form->setParentForm($this);
        $function($form);
        return $this->add($name . '-tab', 'tab', array_merge(['class' => $form, 'template' => 'tab'], $options));
    }

    public function addFieldset($name, $function, array $options = [], $modify = false)
    {
        $model = $this->getModel();
        $form = $this->formBuilder->plain(['model' => $model]);
        $form->setParentForm($this);
        $function($form);
        return $this->add($name . '-fieldset', 'fieldset', array_merge(['class' => $form, 'label' => $name], $options));
    }

    public function addGroup($name, $function)
    {
        $model = $this->getModel();
        $form = $this->formBuilder->plain(['model' => $model]);
        $form->setParentForm($this);
        $function($form);
        $name = $name . '-group';
        return $this->add($name, 'group', ['class' => $form, 'label' => false]);
    }

    public function getLangs()
    {

        $model = $this->getModel();
        if (
            is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')
            && property_exists($model, 'translatable')
        ) {
            return $model->formLanguages;
        }
        return collect([]);
    }

    public function getLangSelector($options = [])
    {
        $model = $this->getModel();
        $langs = $this->getLangs();
        $form = $this->formBuilder->plain();
        if ($langs->where('code', 'new')->first) {
            $selected = $langs->where('code', 'new')->first->code;
        } else {
            if (request()->has('lang') && $langs->where('code', request()->get('lang'))->first()) {
                $selected = request()->get('lang');
            } else {
                if (config('system.front') === true) {
                    $lang_code = App::getLocale();
                } else {
                    $lang_code = session()->get('content_lang')->code;
                }
                if ($langs->where('code', $lang_code)->first()) {
                    $selected = $langs->where('code', $lang_code)->first()->code;
                } else {
                    if ($langs->where('default', 1)->first()) {
                        $selected = $langs->where('default', 1)->first()->code;
                    } else {
                        $selected = $langs->first()->code;
                    }
                }
            }
        }
        $field = $form->makeField('lang_selector', 'select', array_merge($options, [
            'template' => 'lang',
            'selected' => $selected,
            'label' => __('Language'),
            'choices' => $langs->pluck('name', 'code')->toArray(),
            'expanded' => true,
            'multiple' => false
        ]));
        return $form->render([], [$field], false, true, false);
    }

    /**
     * Create a new field and add it to the form.
     *
     * @param  string  $name
     * @param  string  $type
     * @param  array  $options
     * @param  bool  $modify
     * @return BaseFrom
     */
    public function add($name, $type = 'text', array $options = [], $modify = false)
    {
        $this->formHelper->checkFieldName($name, get_class($this));

        if ($this->rebuilding && !$this->has($name)) {
            return $this;
        }

        $model = $this->getModel();
        $langs = false;
        if (
            is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')
            && property_exists($model, 'translatable')
        ) {
            $langs = $model->formLanguages;
        }
        $trans_fields = false;
        if (
            is_subclass_of($model, 'Illuminate\Database\Eloquent\Model')
            && property_exists($model, 'translatable') && $langs && $langs->count() > 0
        ) {
            $trans_fields = $model->translatable;
        }
        if ($trans_fields && in_array($name, $trans_fields)) {
            $form = $this->formBuilder->plain(['model' => $model]);
            foreach ($langs as $lang_model) {
                $lang = $lang_model->code;
                $new_options = $options;

                $labelLocaleIndicator = $this->getConfig(
                    'translatables.label-locale-indicator',
                    '<span>%label%</span> <span class="ml-2 badge badge-pill badge-light">%locale%</span>'
                );
                $localizedLabel = str_replace('%label%', $new_options['label'], $labelLocaleIndicator);

                //$new_options['label'] = str_replace('%locale%', $lang, $localizedLabel);
                $new_options['lang'] = $lang;
                $valueProperty = 'value';
                if ($type == 'select' || $type == 'choice') {
                    $valueProperty = 'selected';
                }
                if ($type == 'radio' || $type == 'checkbox') {
                    $valueProperty = 'checked';
                }
                $new_options[$valueProperty] = function ($value) use ($name, $lang, $model) {
                    return $model->getTranslation($name, $lang);
                };

                $field = $this->makeField($name . '[' . $lang . ']', $type, $new_options);
                $field->setOption(
                    'attr',
                    array_merge(
                        $field->getOption('attr'),
                        [$this->getConfig('translatables.input-locale-attribute', 'data-language') => $lang]
                    )
                );
                $field->setOption(
                    'wrapper.class',
                    $field->getOption('wrapper.class')
                    . ' ' . $this->getConfig(
                        'translatables.form-field-class',
                        'form-field-translation'
                    ) . ' ' . $this->getConfig(
                        'translatables.form-field-class',
                        'form-field-translation'
                    ) . '-' . $lang
                );
                $field->setOption(
                    'wrapper.' . $this->getConfig('translatables.input-locale-attribute', 'data-language'),
                    $lang
                );

                $form->addField($field, $modify);
            }
            $group = $this->makeField($name, 'translation', ['label' => false, 'class' => $form]);
            $this->addField($group, $modify);
        } else {
            $this->addField($this->makeField($name, $type, $options), $modify);
        }
        return $this;
    }

    public function hasType($type)
    {
        try {
            $this->formHelper->getFieldType($type);
        } catch (Exception $e) {
            return false;
        }
        return true;
    }

    /**
     * Renders the rest of the form up until the specified field name.
     *
     * @param  string  $field_name
     * @param  bool  $showFormEnd
     * @param  bool  $showFields
     * @return string
     */
    public function renderField($field_name, $showFormEnd = true, $showFields = true)
    {
        if (!$this->has($field_name)) {
            $this->fieldDoesNotExist($field_name);
        }

        $fields = [$this->all_fields[$field_name]];

        return $this->render([], $fields, false, $showFields, $showFormEnd);
    }

    /**
     * Renders the rest of the form up until the specified field name.
     *
     * @param  string  $field_name
     * @param  bool  $showFormEnd
     * @param  bool  $showFields
     * @return string
     */
    public function usedField($field_name, $showFormEnd = true, $showFields = true)
    {
        $html = $this->renderField($field_name, $showFormEnd, $showFields);
    }

    /**
     * Get single field instance from form object.
     *
     * @param  string  $name
     * @return FormField
     */
    public function getFormField($name)
    {
        if ($this->hasForm($name)) {
            return $this->fields[$name];
        }

        $this->fieldDoesNotExist($name);
    }

    /**
     * Get single field instance from form object.
     *
     * @param  string  $name
     * @return FormField
     */
    public function getField($name)
    {
        if ($this->has($name)) {
            return $this->all_fields[$name];
        }

        $this->fieldDoesNotExist($name);
    }

    /**
     * Check if form has field.
     *
     * @param  string  $name
     * @return bool
     */
    public function has($name)
    {
        return array_key_exists($name, $this->all_fields);
    }

    /**
     * Check if form has field.
     *
     * @param  string  $name
     * @return bool
     */
    public function hasForm($name)
    {
        return array_key_exists($name, $this->fields);
    }

    /**
     * Add a FormField to the form's fields.
     *
     * @param  \Kris\LaravelFormBuilder\Fields\FormField  $field
     * @param  bool  $modify
     * @return $this
     */
    protected function addField(\Kris\LaravelFormBuilder\Fields\FormField $field, $modify = false)
    {
        if (!$this->has($field->getName()) || $modify) {
            $parent = parent::addField($field, $modify);
            if ($field->getType() == 'image') {
                $this->formOptions['files'] = true;
            }
            $this->setParentAllFields($field->getRealName(), $field);
            return $parent;
        }
    }

    public function setParentAllFields($name, $field)
    {
        //dump($this,$this->parent);
        $parent = $this->getParentForm();
        if ($parent) {
            $parent->setParentAllFields($name, $field);
        }
        $this->all_fields[$name] = $field;
    }

    /**
     * Set form options.
     *
     * @param  array  $formOptions
     * @return $this
     */
    public function setFormOptions(array $formOptions)
    {
        $form = parent::setFormOptions($formOptions);
        if (
            isset($this->formOptions['action'])
            && !is_array($this->formOptions['action'])
            && $this->getModel()
        ) {
            $this->formOptions['action'] = [$this->formOptions['action'], $this->getModel()];
        }
        if (
            !isset($this->formOptions['action'])
            && !isset($this->formOptions['route'])
            && !isset($this->formOptions['url'])
            && $this->getModel()
        ) {
            if (Request::route()) {
                $route = Request::route()->getName();
                $route = str_replace(['.create', '.edit'], ['.store', '.update'], $route);
                if (Route::has($route)) {
                    $this->formOptions['route'] = [$route, $this->getModel()];
                    if (Str::contains($route, '.update') && !isset($formOptions['method'])) {
                        $this->formOptions['method'] = 'PUT';
                    }
                }
            }
        }
        return $form;
    }

    /**
     * Redirects to a destination when form is invalid.
     *
     * @param  string|null  $destination  The target url.
     * @return HttpResponseException
     */
    public function redirectIfNotValid($destination = null)
    {
        $request = request();
        if (
            ($request->isMethod('POST') || $request->isMethod('PUT') || $request->isMethod('PATCH'))
            && !$this->isValid()
        ) {
            if (
                ($request->ajax() && !$request->pjax())
                || $request->wantsJson()
            ) {
                $response = new JsonResponse(['type' => 'error', 'errors' => $this->getErrors()], 422);
                throw new HttpResponseException($response);
            }

            $response = redirect($destination);

            if (is_null($destination)) {
                $response = $response->back();
            }

            $response = $response->withErrors($this->getErrors(), $this->getErrorBag())->withInput();

            throw new HttpResponseException($response);
        }
    }

    /**
     * Setup model for form, add namespace if needed for child forms.
     *
     * @return $this
     */
    public function setupModel($model)
    {
        $this->model = $model;
        $this->setupNamedModel();

        return $this;
    }

    /**
     * Validate the form.
     *
     * @param  array  $validationRules
     * @param  array  $messages
     * @return Validator
     */
    public function validate($validationRules = [], $messages = [])
    {
        $fieldRules = $this->formHelper->mergeFieldsRules($this->fields);
        $rules = array_merge($fieldRules->getRules(), $validationRules);
        $messages = array_merge($fieldRules->getMessages(), $messages);
        $trans_lang = config('laravel-form-builder.validation_file', false);
        if ($trans_lang) {
            $language_messages = trans($trans_lang);
//            if (is_array($language_messages)) {
//                $language_messages = array_combine(
//                    array_map(function ($k) {
//                        return '*.' . $k;
//                    }, array_keys($language_messages)),
//                    $language_messages
//                );
//            }
            if (is_array($language_messages)) {
                $messages = array_merge($messages, $language_messages);
            }
        }
        if (isset($this->formOptions['validation_file']) && $this->formOptions['validation_file']) {
            $language_messages = trans($this->formOptions['validation_file']);
//            $language_messages = array_combine(
//                array_map(function ($k) {
//                    return '*.' . $k;
//                }, array_keys($language_messages)),
//                $language_messages
//            );
            if (is_array($language_messages)) {
                $messages = array_merge($messages, $language_messages);
            }
        }
        $this->validator = $this->validatorFactory->make($this->getRequest()->all(), $rules, $messages);
        $this->validator->setAttributeNames($fieldRules->getAttributes());
        $this->eventDispatcher->dispatch(new BeforeFormValidation($this, $this->validator));

        return $this->validator;
    }
}
