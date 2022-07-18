<ul class="nav nav-tabs" role="tablist">

@if ($showField)
    @foreach ((array)$options['children'] as $child)
        @if( ! in_array( $child->getRealName(), (array)$options['exclude']) )
            <li class="nav-item">
                <a class="nav-link @if($child->active()) active @endif" id="{{$child->getRealName()}}-tab" data-toggle="tab" href="#{{$child->getRealName()}}" aria-controls="{{$child->getRealName()}}" role="tab" aria-selected="true">{{$child->label}}</a>
            </li>
        @endif
    @endforeach

    @include('forms.help_block',['options'=>$options])

@endif

</ul>

<div class="tab-content">
    @if ($showField)
        @foreach ((array)$options['children'] as $child)
            @if( ! in_array( $child->getRealName(), (array)$options['exclude']) )
                {!! $child->render() !!}
            @endif
        @endforeach

        @include('forms.help_block',['options'=>$options])

    @endif
</div>

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])
