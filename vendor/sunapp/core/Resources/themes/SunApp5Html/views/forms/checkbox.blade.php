@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div data-id="{{$nameKey}}" @if(isset($options['lang'])) data-lang="true" @endif {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if(isset($options['multiple']))
    @php $is_required = isset($options['attr']['required']) || isset($options['attr']['data-conditionally_required']); @endphp
    @if ($showLabel && $options['label'] !== false && $options['label_show'] && !isset($options['inline']))
        {!! Form::label($name, $options['label'].($is_required ? ' <span class="required-sign">*</span>' : ''), $options['label_attr'], [], false) !!}
    @endif
@endif

@if ($showField)
    <div class="form-control-plaintext custom-control custom-checkbox @if(!isset($options['multiple'])) custom-checkbox--single @endif">
        @if(!isset($options['multiple'])){!! Form::hidden($name,0,['id'=>$options['attr']['id'].'_hidden']) !!}@endif
        @php
            $vue_name = explode('.',$nameKey);
            $vue_name = array_map(function($v){return(is_numeric($v)?'['.$v.']':$v);}, $vue_name);
            $vue_name = implode('.',$vue_name);
            $vue_name = str_replace('.[','[',$vue_name);

            $c_field = (isset($options['lang'])?str_replace('.'.$options['lang'],'',$nameKey):$nameKey);
            $c_field_prefix = 'elementToShow.attributes.';
            $c_field_ex = explode('.',$c_field);
            $c_fields = [];
            $c_last = '';
            foreach($c_field_ex as $ex) {
                $c_fields[] = $c_field_prefix.$c_last.$ex;
                $c_last .= $ex.".";
            }
            if($c_last!=$nameKey) $c_fields[] =  $c_field_prefix.$nameKey;
            $condition = implode(' && ',$c_fields);
        @endphp
        <template v-if="elementToShow && {{$condition}}">
            {!! Form::checkbox($name, $options['value'], $options['checked'], array_merge($options['attr'],['class'=>'custom-control-input', 'v-model' => 'elementToShow.attributes.'.$nameKey])) !!}
        </template>
        <template v-else-if="elementToShow">
            {!! Form::checkbox($name, $options['value'], $options['checked'], array_merge($options['attr'],['class'=>'custom-control-input'])) !!}
        </template>
        <template v-else>
            {!! Form::checkbox($name, $options['value'], $options['checked'], array_merge($options['attr'],['class'=>'custom-control-input'])) !!}
        </template>

        @if(!isset($options['multiple']))
            @if ($showLabel && $options['label'] !== false && $options['label_show'] && !isset($options['inline']))
                {!! Form::label($name, $options['label'].(isset($options['attr']['required']) ? ' <span class="required-sign">*</span>' : ''), array_merge($options['label_attr'],['class'=>'checkbox-label control-label']), [], false) !!}
            @endif
        @endif
        {!! Form::label($options['attr']['id'], (isset($options['inline'])?$options['label']:' '), array_merge($options['label_attr'],['class'=>'custom-control-label']), [] ,false) !!}
    </div>
    @include('forms.help_block',['options'=>$options])
@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
