@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div data-id="{{$nameKey}}" @if(isset($options['lang'])) data-lang="true" @endif {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    @php
        $is_required = isset($options['attr']['required']) || isset($options['attr']['data-conditionally_required']);
        $label = $options['label'].($is_required ? ' <span class="required-sign">*</span>' : '').(isset($options['lang'])?'<span class="ml-1 badge badge-pill badge-light">'.$options['lang'].'</span>':'').'<span class="ml-1 other-lang-error hidden" title="'.trans('core::messages.error_in_other_lang').'"><i class="feather icon-alert-triangle"></i></span>';
        if(isset($options['other_lang_error']) && $options['other_lang_error'] && !in_array($nameKey,$options['other_lang_error'])) $label = $label.' <span class="ml-1 badge badge-pill badge-warning"><i class="feather icon-alert-triangle"></i></span>';
    @endphp
    {!! Form::label($name, $label, $options['label_attr'], [], false) !!}
@endif

@if ($showField)
        <template v-if="elementToShow">
            @php
                $c_field = (isset($options['lang'])?str_replace('.'.$options['lang'],'',$nameKey):$nameKey);
                $c_field_prefix = 'elementToShow.attributes.placeholder_';
                $c_field_prefix_name = 'elementToShow.attributes.';
                $c_field_ex = explode('.',$c_field);
                $c_fields = [];
                $c_fields_name = [];
                $c_last = '';
                foreach($c_field_ex as $ex) {
                    $c_fields[] = $c_field_prefix.$c_last.$ex;
                    $c_fields_name[] = $c_field_prefix_name.$c_last.$ex;
                    $c_last .= $ex.'.';
                }
                if ($c_last!=$nameKey) {
                    $c_fields[] =  $c_field_prefix.$nameKey;
                    if ($c_field_prefix_name.$nameKey !== $c_fields_name[count($c_fields_name)-1]) {
                        $c_fields_name[] =  $c_field_prefix_name.$nameKey;
                    }
                }
                $condition = implode(' && ',$c_fields);

                array_pop($c_fields_name);
                $condition_name = implode(' && ',$c_fields_name);
            @endphp
            <template v-if="elementToShow && {{$condition_name ? $condition_name . ' && ' : ''}}typeof elementToShow.attributes.{{$nameKey}} !== 'undefined'">
                {!! Form::textarea($name, $options['value'], array_merge($options['attr'],['v-model' => 'elementToShow.attributes.'.$nameKey])) !!}
            </template>
            <template v-else>
                @php
                    $inputValue = '';
                @endphp
                {!! Form::textarea($name, $inputValue, $options['attr']) !!}
            </template>
        </template>
        <template v-else>
            @php
                $inputValue = '';
            @endphp
            {!! Form::textarea($name, $inputValue, $options['attr']) !!}
        </template>

    @include('forms.help_block',['options'=>$options])
@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
