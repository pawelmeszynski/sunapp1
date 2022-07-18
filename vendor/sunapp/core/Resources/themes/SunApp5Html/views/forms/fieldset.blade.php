@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <fieldset {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    <legend>{{$options['label']}}</legend>
@endif; ?>

@if ($showField)
    @foreach ((array)$options['children'] as $child)
        @if( ! in_array( $child->getRealName(), (array)$options['exclude']) )
            {!! $child->render() !!}
        @endif
    @endforeach

    @include('forms.help_block',['options'=>$options])

@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </fieldset>
    @endif
@endif
