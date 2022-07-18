@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div class="form-group language-selector" >
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    {!! Form::label($name, $options['label'], $options['label_attr']) !!}
@endif

@if ($showField)
    @php $emptyVal = $options['empty_value'] ? ['' => $options['empty_value']] : null; @endphp
    {!! Form::select($name, (array)$emptyVal + $options['choices'], $options['selected'], $options['attr']) !!}
    @include('forms.help_block',['options'=>$options])
@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
