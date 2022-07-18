@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    <div {!! $options['wrapperAttrs'] !!}>
    @endif
@endif

@if ($showLabel && $options['label'] !== false && $options['label_show'])
    {!! Form::label($name, $options['label'].(isset($options['attr']['required']) ? ' <span class="required-sign">*</span>' : ''), $options['label_attr']) !!}
@endif

@if ($showField)
    <table>
        <thead>
            <tr>
                <th></th>
                @foreach((array)$options['axis_b'] as $axis_b)
                    <th>{{$axis_b['label']}}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @foreach((array)$options['axis_a'] as $axis_a_key=>$axis_a)
            <tr>
                <th>{{$axis_a['label']}}</th>
                @foreach((array)$options['axis_b'] as $axis_b_key=>$axis_b)
                    <td>{!! (isset($options['axis_a'][$axis_a_key][$axis_b_key]['field'])?$options['axis_a'][$axis_a_key][$axis_b_key]['field']->render([],false):'') !!}</td>
                @endforeach
            </tr>
            @endforeach
        </tbody>
    </table>
    @include('forms.help_block',['options'=>$options])

@endif

@include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
    </div>
    @endif
@endif
