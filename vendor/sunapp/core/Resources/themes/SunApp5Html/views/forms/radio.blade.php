@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div data-id="{{$nameKey}}" @if(isset($options['lang'])) data-lang="true" @endif {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

        @if ($showLabel && $options['label'] !== false && $options['label_show'] && !isset($options['inline']))
            {!! Form::label($options['attr']['id'], $options['label'], $options['label_attr'], [], false) !!}
        @endif

@if ($showField)
            <div class="form-control-plaintext custom-control custom-radio">
                <template v-if="elementToShow">
                    {!! Form::radio($name, $options['value'], $options['checked'], array_merge($options['attr'],['class'=>'custom-control-input', 'v-model' => 'elementToShow.attributes.'.$nameKey])) !!}
                </template>
                <template v-else>
                    {!! Form::radio($name, $options['value'], $options['checked'], array_merge($options['attr'],['class'=>'custom-control-input'])) !!}
                </template>

    @if ($showLabel && $options['label'] !== false && $options['label_show'] && isset($options['inline']))
        {!! Form::label($options['attr']['id'], $options['label'], array_merge($options['label_attr'],['class'=>'custom-control-label'])) !!}
    @endif
            </div>
    @include('forms.help_block',['options'=>$options])
@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
