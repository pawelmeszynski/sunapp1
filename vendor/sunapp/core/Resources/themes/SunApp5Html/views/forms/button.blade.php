@if ($options['wrapper'] !== false)
<div {!! $options['wrapperAttrs'] !!}>
@endif

{!! Form::button($options['label'], array_merge($options['attr'],['class'=>'btn '.($options['attr']['type']=='submit'?'btn-primary':'btn-light').' mr-1 mb-1 waves-effect waves-light'])) !!}
@include('forms.help_block',['options'=>$options])

@if ($options['wrapper'] !== false)
</div>
@endif
