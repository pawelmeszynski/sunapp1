<!DOCTYPE html>
<html class="loading" lang="@locale" lang-fallback="@localeFallback" data-textdirection="ltr">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="keywords" content="@get('keywords')">
    <meta name="description" content="@get('description')">
    <meta name="author" content="@get('author')">
    <meta name="csrf-token" content="@csrfToken">
    <meta name="theme-assets-url" content="@asset('')">
    <meta name="base-url" content="{{url('/')}}">
    <meta name="admin-base-url" content="{{url(env('APP_PREFIX',''))}}"/>

    <title>@get('title', config('app.name', 'SunApp'))</title>

    <link rel="shortcut icon" type="image/x-icon" href="@asset('images/ico/favicon.ico')">
    <meta name="theme-color" content="#ffd800">
    <meta name="msapplication-navbutton-color" content="#ffd800">
    <meta name="apple-mobile-web-app-status-bar-style" content="#ffd800">
    <meta name="msapplication-TileColor" content="#ffd800">
    <link rel="apple-touch-icon" sizes="57x57" href="@asset('images/ico/apple-icon-57x57.png')">
    <link rel="apple-touch-icon" sizes="60x60" href="@asset('images/ico/apple-icon-60x60.png')">
    <link rel="apple-touch-icon" sizes="72x72" href="@asset('images/ico/apple-icon-72x72.png')">
    <link rel="apple-touch-icon" sizes="76x76" href="@asset('images/ico/apple-icon-76x76.png')">
    <link rel="apple-touch-icon" sizes="114x114" href="@asset('images/ico/apple-icon-114x114.png')">
    <link rel="apple-touch-icon" sizes="120x120" href="@asset('images/ico/apple-icon-120x120.png')">
    <link rel="apple-touch-icon" sizes="144x144" href="@asset('images/ico/apple-icon-144x144.png')">
    <link rel="apple-touch-icon" sizes="152x152" href="@asset('images/ico/apple-icon-152x152.png')">
    <link rel="apple-touch-icon" sizes="180x180" href="@asset('images/ico/apple-icon-180x180.png')">
    <link rel="icon" type="image/png" sizes="192x192"  href="@asset('images/ico/android-icon-192x192.png')">
    <link rel="icon" type="image/png" sizes="32x32" href="@asset('images/ico/favicon-32x32.png')">
    <link rel="icon" type="image/png" sizes="96x96" href="@asset('images/ico/favicon-96x96.png')">
    <link rel="icon" type="image/png" sizes="16x16" href="@asset('images/ico/favicon-16x16.png')">
    <meta name="msapplication-TileImage" content="@asset('images/ico/ms-icon-144x144.png')">
    <link rel="manifest" href="@asset('images/ico/manifest.json')">

    {{-- Include core + vendor Styles --}}
    @partial('styles')

    {{-- Include page Style --}}
    @yield('mystyle')

    @styles()

</head>

<body class="vertical-layout vertical-menu-modern 1-column  navbar-floating footer-static bg-full-screen-image  blank-page blank-page" data-open="click" data-menu="vertical-menu-modern" data-col="1-column">

<!-- BEGIN: Content-->
<!-- BEGIN: Content-->
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-body">
            @content()
        </div>
    </div>
</div>
<!-- End: Content-->

{{-- include default scripts --}}
@partial('scripts')

{{-- Include page script --}}
@yield('myscript')
@scripts()
</body>

</html>
