{!! form_start($form,['id'=>'form-data','novalidate'=>'novalidate','method'=>'POST']) !!}
<input type="hidden" v-if="itemEdition" name="_method" value="PATCH">
<div class="form-body">
    <ul class="nav nav-tabs" role="tablist">
        <li class="nav-item">
            <a class="nav-link active" id="info-tab" data-toggle="tab" href="#info"
               aria-controls="info" role="tab"
               aria-selected="true">@lang('core::actions.info')</a>
        </li>
        @if($form->hasType('server_files'))
            <li class="nav-item">
                <a class="nav-link" id="media-tab" data-toggle="tab" href="#media"
                   aria-controls="media" role="tab"
                   aria-selected="true">@lang('core::users.media')</a>
            </li>
        @endif
        @include('core::extra-fields.tab')
    </ul>
    <div class="tab-content">
        <div class="tab-pane active" id="info" aria-labelledby="info-tab" role="tabpanel">
            <div class="row">
                @if ($form->has('name'))
                    <div class="col-md-4 col-12">
                        {!! form_manual($form, 'name') !!}
                        <template v-if="elementToShow">
                            <div data-id="name" class="form-group">
                                <label for="name" class="control-label">@lang('core::users.name')</label>
                                <input v-if="elementToShow.attributes.is_ldap"  data-force_readonly="1" readonly disabled required="required" name="name" type="text" id="name" class="form-control" v-model="elementToShow.attributes.name">
                                <input v-else required="required" name="name" type="text" id="name" class="form-control" v-model="elementToShow.attributes.name">
                                <div class="form-error"></div>
                            </div>
                        </template>
                        <template v-else>
                            <div data-id="name" class="form-group">
                                <label for="name" class="control-label">@lang('core::users.name')</label>
                                <input required="required" name="name" type="text" id="name" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                    </div>
                @endif

                @if ($form->has('ldap_name'))
                    <div class="col-md-4 col-12">
                        {!! form_manual($form, 'ldap_name') !!}
                        <template v-if="elementToShow">
                            <div data-id="ldap_name" class="form-group">
                                <label for="ldap_name" class="control-label">@lang('core::users.ldap_name')</label>
                                <input v-if="elementToShow.attributes.is_ldap"  data-force_readonly="1" readonly disabled required="required" name="ldap_name" type="text" id="ldap_name" class="form-control" v-model="elementToShow.attributes.ldap_name">
                                <input v-else readonly disabled  name="ldap_name" type="text" id="ldap_name" class="form-control" v-model="elementToShow.attributes.ldap_name">
                                <div class="form-error"></div>
                            </div>
                        </template>
                        <template v-else>
                            <div data-id="ldap_name" class="form-group">
                                <label for="ldap_name" class="control-label">@lang('core::users.ldap_name')</label>
                                <input readonly disabled  data-force_readonly="1" name="ldap_name" type="text" id="ldap_name" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                    </div>
                @endif

                @if ($form->has('email'))
                    <div class="col-md-4 col-12">
                        {!! form_manual($form, 'email') !!}
                        <template v-if="elementToShow">
                            <div data-id="email" class="form-group">
                                <label for="email" class="control-label">@lang('core::users.email')</label>
                                <input v-if="elementToShow.attributes.is_ldap" data-force_readonly="1" readonly disabled required="required" name="email" type="email" id="email" class="form-control" v-model="elementToShow.attributes.email">
                                <input v-else required="required" name="email" type="email" id="email" class="form-control" v-model="elementToShow.attributes.email">
                                <div class="form-error"></div>
                            </div>
                        </template>
                        <template v-else>
                            <div data-id="email" class="form-group">
                                <label for="email" class="control-label">@lang('core::users.email')</label>
                                <input required="required" name="email" type="email" id="email" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                    </div>
                @endif

                @if ($form->has('password'))
                    <div class="col-md-4 col-12">
                        {!! form_manual($form, 'password') !!}
                        <template v-if="elementToShow">
                            <div data-id="password" class="form-group">
                                <label for="password" class="control-label d-block">@lang('core::users.password') <span class="password-item" v-if="!elementToShow.attributes.is_ldap && !itemPreview"><span @click.prevent="generatePassword(8, 'password')">@lang('core::users.generate_password')</span> / <span @click.prevent="copyValue('password')">@lang('core::users.copy')</span></span></label>
                                <input v-if="elementToShow.attributes.is_ldap" data-force_readonly="1" readonly disabled name="password" type="password" id="password" class="form-control">
                                <input v-else name="password" type="password" id="password" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                        <template v-else>
                            <div data-id="password" class="form-group">
                                <label for="password" class="control-label d-block">@lang('core::users.password') <span class="password-item"><span @click.prevent="generatePassword(8, 'password')">@lang('core::users.generate_password')</span> / <span @click.prevent="copyValue('password')">@lang('core::users.copy')</span></span></label>
                                <input required="required" name="password" type="password" id="password" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                    </div>
                @endif

                @if ($form->has('password_confirmation'))
                    <div class="col-md-4 col-12">
                        {!! form_manual($form, 'password_confirmation') !!}
                        <template v-if="elementToShow">
                            <div data-id="password_confirmation" class="form-group">
                                <label for="password_confirmation" class="control-label">@lang('core::users.password_confirmation')</label>
                                <input v-if="elementToShow.attributes.is_ldap" readonly data-force_readonly="1" disabled name="password_confirmation" type="password" id="password_confirmation" class="form-control">
                                <input v-else name="password_confirmation" type="password" id="password_confirmation" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                        <template v-else>
                            <div data-id="password_confirmation" class="form-group">
                                <label for="password_confirmation" class="control-label">@lang('core::users.password_confirmation')</label>
                                <input required="required" name="password_confirmation" type="password" id="password_confirmation" class="form-control">
                                <div class="form-error"></div>
                            </div>
                        </template>
                    </div>
                @endif

                @if ($form->has('user_group'))
                    <div class="col-12 col-md-4">
                        {!! form_field($form, 'user_group') !!}
                    </div>
                @endif

                @if ($form->has('api_token'))
                    <div class="col-12 col-md-8">
                        {!! form_manual($form, 'api_token') !!}
                        <div data-id="api_token" class="form-group">
                            <label for="api_token" class="control-label d-block">
                                @lang('core::users.api_token')
                                <span class="password-item" v-if="!itemPreview">
                                                <span class="enable-field" @click.prevent="enableField('api_token', $event)">@lang('core::actions.enable_field') /</span>
                                                <span @click.prevent="generatePassword([64, 250], 'api_token')">@lang('core::users.generate_token')</span>
                                                / <span @click.prevent="copyValue('api_token')">@lang('core::users.copy')</span>
                                            </span>
                            </label>
                            <textarea name="api_token" readonly id="api_token" class="form-control" rows="3" data-force_readonly="1"></textarea>
                            <div class="form-error"></div>
                        </div>
                    </div>
                @endif

                @if ($form->has('user_role'))
                    <div class="col-12 col-md-4">
                        {!! form_field($form, 'user_role') !!}
                    </div>
                @endif

                @if ($form->has('email_verify'))
                    <div class="col-md-4 col-12">
                        {!! form_manual($form, 'email_verify') !!}
                        <template v-if="(elementToShow && !elementToShow.attributes.email_verified_at) || !elementToShow">
                            <div data-id="email_verify" class="form-group">
                                <label for="email_verify" class="control-label">@lang('core::users.activate_account')</label>
                                <div class="form-control-plaintext custom-control custom-checkbox">
                                    <input id="email_verify_hidden" name="email_verify" type="hidden" value="0">
                                    <input id="email_verify" checked="checked" name="email_verify" type="checkbox" value="1" class="custom-control-input">
                                    <label for="email_verify" class="custom-control-label"></label>
                                </div>
                                <div class="form-error"></div>
                            </div>
                        </template>
                    </div>
                @endif
            </div>
        </div>

        @if($form->hasType('server_files'))
            <div class="tab-pane" id="media" aria-labelledby="media-tab" role="tabpanel">
                <div class="row">
                    <div class="col-12">
                        {!! form_field($form,'images') !!}
                    </div>
                </div>
            </div>
        @endif

        @include('core::extra-fields.content')
    </div>
</div>
{!! form_end($form) !!}
