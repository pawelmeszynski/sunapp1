@layout('auth')

<section class="row flexbox-container">
    <div class="col-xl-8 col-11 d-flex justify-content-center">
        <div class="card bg-authentication rounded-0 mb-0">
            <div class="row m-0">
                <div class="col-lg-6 d-lg-block d-none text-center align-self-center px-1 py-0">
                    <img src="@asset('../app-assets/images/pages/login.png')" alt="branding logo">
                </div>
                <div class="col-lg-6 col-12 p-0">
                    <div class="card rounded-0 mb-0 px-0">
                        <div class="card-header pb-1">
                            <div class="card-title">
                                <h4 class="mb-0">@lang('auth.verify_2fa_authentication')</h4>
                            </div>
                        </div>
                        @if(Auth::user()->getVerifiedAt2faGoogle() == null)
                            <p class="px-2">@lang('auth.verify_2fa_app_register_instruction')</p>
                        @else
                            <p class="px-2">@lang('auth.verify_2fa_app_code_instruction')</p>
                        @endif
                        <div class="card-content">
                            <div class="card-body pt-1">
                                @if(Auth::user()->getVerifiedAt2faGoogle() == null)
                                    <div class="row">
                                        <div class="col-6">
                                            <div class="d-flex justify-content-center">
                                                <div class="img-thumbnail">
                                                    <img class="img-fluid" src="{{Auth::user()->getQrCode2faGoogle()}}">
                                                </div>
                                            </div>
                                            <br>
                                        </div>
                                        <div class="col-6">
                                            <div class="d-flex justify-content-center">
                                                <div class="img-thumbnail">
                                                    <div class="position-relative">
                                                        <img class="img-fluid" style="opacity: 0;" src="{{Auth::user()->getQrCode2faGoogle()}}">
                                                        <div class="position-absolute fixed-top">
                                                            <div class="p-2">
                                                                @lang('auth.verify_2fa_alternate_code')<br>
                                                                <strong>{{ Auth::user()->google2fa_secret }}</strong>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            <br>
                                        </div>
                                    </div>
                                @endif
                                <form method="POST" action="{{ route('SunApp::2fa') }}">
                                    {{ csrf_field() }}

                                    <fieldset class="form-label-group form-group position-relative has-icon-left">
                                        <input type="number"
                                               class="form-control @error('one_time_password') is-invalid @enderror"
                                               id="one_time_password" name="one_time_password"
                                               placeholder="@lang('auth.verify_2fa_one_time_password')"
                                               value="{{ old('one_time_password') }}" required
                                               autocomplete="one_time_password" autofocus>
                                        <div class="form-control-position">
                                            <i class="feather icon-user"></i>
                                        </div>
                                        <label for="user-name">@lang('auth.verify_2fa_one_time_password')</label>
                                        @error('one_time_password')
                                        <span class="invalid-feedback" role="alert">
                                                        {{ $message }}
                                                    </span>
                                        @enderror
                                    </fieldset>

                                    <a onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                                       href="{{ route('SunApp::logout') }}"
                                       class="btn btn-secondary float-right btn-inline ml-1">@lang('auth.logout')
                                    </a>

                                    <button type="submit"
                                            class="btn btn-primary float-right btn-inline">@lang('auth.login')</button>
                                </form>
                                    <form id="logout-form" action="{{ route('SunApp::logout') }}" method="POST">
                                        @csrf
                                    </form>
                            </div>
                        </div>
                        <div class="login-footer">
                            <div class="divider">
                            </div>
                            <div class="footer-btn d-inline">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
