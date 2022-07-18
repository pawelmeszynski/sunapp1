@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div data-id="{{$nameKey}}" @if(isset($options['lang'])) data-lang="true" @endif {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    {!! Form::label($name, $options['label'], $options['label_attr']) !!}
@endif

@if ($showField)
    <{{$options['tag']}} {{$options['elemAttrs']}}>{{$options['value']}}</{{$options['tag']}}>

    @include('forms.help_block',['options'=>$options])

@endif


@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
