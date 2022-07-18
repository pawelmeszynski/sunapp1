@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div {!! $options['wrapperAttrs'] !!} >
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'] && count((array)$options['children'])>1)
    {!! Form::label($name, $options['label'], $options['label_attr']) !!}
@elseif ($showLabel && $options['label'] !== false && $options['label_show'] && count((array)$options['children'])<=1)
    @if(!isset($options['attr']['data-url']))
        {!! Form::label($name, $options['label'].(isset($options['attr']['required']) ? ' <span class="required-sign">*</span>' : ''), $options['label_attr']) !!}
    @else
        {!! Form::label($name, $options['label'].(isset($options['attr']['required']) ? ' <span class="required-sign">*</span>' : '').' '.view('forms.refresh_icon',['data_url'=>$options['attr']['data-url'],'name'=>$name]), $options['label_attr'],false) !!}
    @endif
@endif

@if ($showField)
    @if(count((array)$options['children'])>1)
        <ul class="list-unstyled mb-0">
            @foreach ((array)$options['children'] as $child)
                <li class="d-inline-block mr-2">
                    <fieldset>
                        {!! $child->render(array_merge($options['choice_options'],['inline'=>true,'multiple'=>true]), true, true, false) !!}
                    </fieldset>
                </li>
            @endforeach
        </ul>
    @else

        {!! $options['children'][0]->render(array_merge($options['choice_options'],['inline'=>true,'multiple'=>true]), true, true, false) !!}

    @endif
    @include('forms.help_block',['options'=>$options])

@endif


@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
