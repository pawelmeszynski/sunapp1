@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

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
    </div>
    @endif
@endif
