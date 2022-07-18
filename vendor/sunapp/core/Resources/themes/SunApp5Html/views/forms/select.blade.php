@if ($showLabel && $showField)
    @if ($options['wrapper'] !== false)
        <div data-id="{{$nameKey}}" @if(isset($options['lang'])) data-lang="true" @endif {!! $options['wrapperAttrs'] !!} >
            @endif
            @endif

            @if ($showLabel && $options['label'] !== false && $options['label_show'])
                @php $is_required = isset($options['attr']['required']) || isset($options['attr']['data-conditionally_required']); @endphp
                @if(!isset($options['attr']['data-url']) || (isset($options['attr']['data-url']) && isset($options['attr']['data-prevent-refresh'])))
                    {!! Form::label($name, $options['label'].($is_required ? ' <span class="required-sign">*</span>' : ''), $options['label_attr'], false) !!}
                @else
                    {!! Form::label($name, $options['label'].($is_required ? ' <span class="required-sign">*</span>' : '').' '.view('forms.refresh_icon',['data_url'=>$options['attr']['data-url'],'name'=>$name]), $options['label_attr'],false) !!}
                @endif
            @endif

            @if ($showField)
                @php $emptyVal = $options['empty_value'] ? ['' => $options['empty_value']] : null; @endphp
                @php
                    if (is_object($options['selected']) && !is_a($options['selected'], 'Illuminate\Database\Eloquent\Collection')) {
                        $entity = $options['selected'];
                        $key = $entity->getKeyName();
                        $options['selected'] = $entity->{$key};
                    }
                @endphp
                <template v-if="elementToShow">
                    @php
                        $c_field = (isset($options['lang'])?str_replace('.'.$options['lang'],'',$nameKey):$nameKey);
                        $c_field_prefix_name = 'elementToShow.attributes.';
                        $c_field_ex = explode('.',$c_field);
                        $c_fields_name = [];
                        $c_last = '';
                        foreach($c_field_ex as $ex) {
                            $c_fields_name[] = $c_field_prefix_name.$c_last.$ex;
                            $c_last .= $ex.'.';
                        }
                        if ($c_last!=$nameKey) {
                            if ($c_field_prefix_name.$nameKey !== $c_fields_name[count($c_fields_name)-1]) {
                                $c_fields_name[] =  $c_field_prefix_name.$nameKey;
                            }
                        }
                        array_pop($c_fields_name);
                        $condition_name = implode(' && ',$c_fields_name);
                    @endphp
                    <template v-if="elementToShow && {{$condition_name ? $condition_name . ' && ' : ''}}typeof elementToShow.attributes.{{$nameKey}} !== 'undefined'">
                        {!! Form::select($name, (array)$emptyVal + $options['choices'], $options['selected'], array_merge($options['attr'],['class'=>$options['attr']['class'].(isset($options['tree']) && $options['tree']?' treeselect':''), 'v-model' => 'elementToShow.attributes.'.$nameKey, 'v-select2', 'v-on:change' => 'changedField("'.$name.'")']), (isset($options['options_attr'])?$options['options_attr']:[])) !!}
                    </template>
                    <template v-else>
                        {!! Form::select($name, (array)$emptyVal + $options['choices'], $options['selected'], array_merge($options['attr'],['class'=>$options['attr']['class'].(isset($options['tree']) && $options['tree']?' treeselect':''), 'v-select2', 'v-on:change' => 'changedField("'.$name.'")']), (isset($options['options_attr'])?$options['options_attr']:[])) !!}
                    </template>
                </template>
                <template v-else>
                    {!! Form::select($name, (array)$emptyVal + $options['choices'], $options['selected'], array_merge($options['attr'], ['class'=>$options['attr']['class'].(isset($options['tree']) && $options['tree']?' treeselect':''), 'v-on:change' => 'changedField("'.$name.'")']), (isset($options['options_attr'])?$options['options_attr']:[])) !!}
                </template>
                @include('forms.help_block',['options'=>$options])
            @endif

            @include('forms.errors',['options'=>$options,'errors'=>$errors, 'showError'=>$showError, 'errorBag'=>$errorBag])

            @if ($showLabel && $showField)
                @if ($options['wrapper'] !== false)
        </div>
    @endif
@endif
