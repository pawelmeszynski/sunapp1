@if(method_exists($item, 'extraFields'))
    @if($item->extraFields()->count())
        @php
            $extraFields = $item->extraFields()->get();
            $fullWidthFields = ['editor', 'textarea'];
            foreach($extraFields as $extraField) {
                    $label = $extraField->name;
                    $extra_name = "extra.".Str::slug($label, '_');
                    if ($extraField->translatable) {
                        $form->getModel()->translatable = array_merge(
                            $form->getModel()->translatable,
                            ["extra.".Str::slug($extraField->name, '_')]
                        );
                    }
                    if ($extraField->type == 'server_files') {
                        $label = $extra_name = "extra_".Str::slug($label, '_');
                    }
                    $form->add($extra_name, $extraField->type,
                        array_merge(['label'=>$label],json_decode($extraField->options,true))
                );
            }
        @endphp
        <div class="tab-pane" id="extraFields" aria-labelledby="extraFields-tab" role="tabpanel">
            <div class="row">
                @foreach($extraFields as $extraField)
                    @php
                        $extra_name = "extra.".Str::slug($extraField->name, '_');
                        if ($extraField->type == 'server_files') {
                            $extra_name = "extra_".Str::slug($extraField->name, '_');
                        }
                    @endphp
                    <div class="@if( !in_array($extraField->type, $fullWidthFields))col-md-6 @endif col-12">
                        {!! form_field($form, $extra_name) !!}
                    </div>
                @endforeach
            </div>
        </div>
    @endif
@endif
