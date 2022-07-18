@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if ($showField)
    @php
        $other_lang_error = false;
        if(isset($errors) && $errors->hasBag($errorBag)) {
            foreach((array)$options['children'] as $child) {
                $er = $errors->getBag($errorBag)->get($child->getNameKey());
                if($er) $other_lang_error[] = $child->getNameKey();
            }
        }
    @endphp
    @foreach ((array)$options['children'] as $child)
        @if( ! in_array( $child->getRealName(), (array)$options['exclude']) )
            @php  @endphp
            {!! $child->render(['other_lang_error'=>$other_lang_error]) !!}
        @endif
    @endforeach

    @include('forms.help_block',['options'=>$options])

@endif



@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif

