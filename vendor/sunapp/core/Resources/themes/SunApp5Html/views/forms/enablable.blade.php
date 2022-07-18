@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div data-id="{{$nameKey}}" @if(isset($options['lang'])) data-lang="true" @endif {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    @php
        $label = $options['label'].'<span class="password-item"><span class="enable-field" @click.prevent="enableField(\''.$nameKey.'\', $event)">'.trans('core::actions.enable_field').'</span></span>';
        if(isset($options['other_lang_error']) && $options['other_lang_error'] && !in_array($nameKey,$options['other_lang_error'])) $label = $label.' <span class="ml-1 badge badge-pill badge-warning"><i class="feather icon-alert-triangle"></i></span>';
    @endphp
    {!! Form::label($name, $label, $options['label_attr'], [], false) !!}
@endif

@if ($showField)
    <template v-if="elementToShow">
        @php
            $c_field = (isset($options['lang'])?str_replace('.'.$options['lang'],'',$nameKey):$nameKey);
            $c_field_prefix = 'elementToShow.attributes.placeholder_';
            $c_field_ex = explode('.',$c_field);
            $c_fields = [];
            $c_last = '';
            foreach($c_field_ex as $ex) {
                $c_fields[] = $c_field_prefix.$c_last.$ex;
                $c_last .= $ex.'.';
            }
            if($c_last!=$nameKey) $c_fields[] =  $c_field_prefix.$nameKey;
            $condition = implode(' && ',$c_fields);
        @endphp
        {!! Form::input('text', $name, $options['value'], array_merge($options['attr'], ['data-force_readonly' => '1', 'disabled' => 'disabled', 'v-model' => 'elementToShow.attributes.'.$nameKey,':placeholder' => $condition.'?elementToShow.attributes.placeholder_'.$nameKey.':""'])) !!}
    </template>
    <template v-else>
        {!! Form::input('text', $name, $options['value'], array_merge($options['attr'], ['data-force_readonly' => '1'])) !!}
    </template>

    @include('forms.help_block',['options'=>$options])
@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
