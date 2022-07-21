@layout('auth')

<section class="row flexbox-container">
    <div class="col-xl-8 col-11 d-flex justify-content-center">
        <div class="card bg-authentication rounded-0 mb-0">
            <div class="row m-0">
                <div class="col-lg-6 d-lg-block d-none text-center align-self-center px-1 py-0">
                    <img src="@asset('../app-assets/images/pages/login.png')" alt="branding logo">
                </div>
                <div class="col-lg-6 col-12 p-0">
                    <div class="card rounded-0 mb-0 px-2">
                        <div class="card-header pb-1">
                            <div class="card-title">
                                <h4 class="mb-0">@lang('auth.login')</h4>
                            </div>
                        </div>
                        <p class="px-2">@lang('auth.login_message')</p>
                        <div class="card-content">
                            <div class="card-body pt-1">
                                <form method="POST" action="{{ route('SunApp::sunbet.login') }}">
                                    @csrf
                                    <fieldset class="form-label-group form-group position-relative has-icon-left">
                                        <input type="email" class="form-control @error('email') is-invalid @enderror"
                                               id="user-email" name="email" placeholder="@lang('auth.email')"
                                               value="{{ old('email') }}" required autocomplete="email" autofocus>
                                        <div class="form-control-position">
                                            <i class="feather icon-user"></i>
                                        </div>
                                        <label for="user-name">@lang('auth.email')</label>
                                        @error('email')
                                        <span class="invalid-feedback" role="alert">
                                        {{ $message }}
                                        </span>
                                        @enderror
                                    </fieldset>
                                    <fieldset class="form-label-group position-relative has-icon-left">
                                        <input type="password"
                                               class="form-control @error('password') is-invalid @enderror"
                                               id="user-password" name="password" placeholder="@lang('auth.password')"
                                               required autocomplete="current-password">
                                        <div class="form-control-position">
                                            <i class="feather icon-lock"></i>
                                        </div>
                                        <label for="user-password">@lang('auth.password')</label>
                                        @error('password')
                                        <span class="invalid-feedback" role="alert">
                                        {{ $message }}
                                        </span>
                                        @enderror
                                    </fieldset>
                                    <button type="submit"
                                            class="btn btn-primary float-right btn-inline">@lang('auth.login')</button>
                                    <input type="hidden" name="_token" value="{{ Session::token() }}">
                                    <!-- Verify condition -->
                                    @if(isset($_GET['redirectTo']))
                                        <input type="hidden" name="redirectTo" value="{{ $_GET['redirectTo'] }}">
                                    @endif
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

