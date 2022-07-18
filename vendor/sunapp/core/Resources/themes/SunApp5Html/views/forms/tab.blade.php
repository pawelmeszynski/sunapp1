<div class="tab-pane @if($active) active @endif" id="{{$name}}" aria-labelledby="{{$name}}-tab" role="tabpanel">
    @if ($showField)
        @foreach ((array)$options['children'] as $child)
            @if( ! in_array( $child->getRealName(), (array)$options['exclude']) )
                {!! $child->render() !!}
            @endif
        @endforeach

        @include('forms.help_block',['options'=>$options])

    @endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])
</div>
