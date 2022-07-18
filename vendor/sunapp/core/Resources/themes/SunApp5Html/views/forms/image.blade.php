@if($showLabel && $showField)
    @if($options['wrapper'] !== false)
        <div {!! $options['wrapperAttrs'] !!} >
            @endif
            @endif

            @if($showLabel && $options['label'] !== false && $options['label_show'])
                {!! Form::customLabel($name, $options['label'], $options['label_attr']) !!}
            @endif

            @if($showField)
                {!! Form::file($name, $options['attr']) !!}

                @include('forms.help_block', ['options' => $options])
            @endif

            @include('forms.errors', ['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

            @if($showLabel && $showField)
                @if($options['wrapper'] !== false)
        </div>
    @endif
@endif
