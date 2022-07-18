<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ trans('modules.title') }}@if (trim($__env->yieldContent('template_title')))
            | @yield('template_title')@endif</title>
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,600" rel="stylesheet" type="text/css">
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css" rel="stylesheet"/>
    <link href="{{ asset('installer/css/style.css') }}" rel="stylesheet"/>
    @yield('style')
    <script>
        window.Laravel = (_ => _)(<?php echo json_encode([
            'csrfToken' => csrf_token(),
        ]); ?>)
    </script>
</head>
<body>
<div class="master">
    <div class="box">
        <div class="header">
            <h1 class="header__title">@yield('title')</h1>
        </div>
        <div class="main">
            @if (session('message'))
                <p class="alert text-center">
                    <strong>
                        @if(is_array(session('message')))
                            {{ session('message')['message'] }}
                        @else
                            {{ session('message') }}
                        @endif
                    </strong>
                </p>
            @endif
            @if(session()->has('errors'))
                <div class="alert alert-danger" id="error_alert">
                    <button type="button" class="close" id="close_alert" data-dismiss="alert" aria-hidden="true">
                        <i class="fa fa-close" aria-hidden="true"></i>
                    </button>
                    <h4>
                        <i class="fa fa-fw fa-exclamation-triangle" aria-hidden="true"></i>
                        {{ trans('modules.forms.errorTitle') }}
                    </h4>
                    <ul>
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            @yield('container')
        </div>
    </div>
</div>
<script src="https://code.jquery.com/jquery-3.3.1.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"></script>
@yield('scripts')

</body>
</html>
