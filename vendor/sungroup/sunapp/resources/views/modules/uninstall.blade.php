@extends('modules.layout')

@section('template_title')
    @lang('modules.uninstalling') "{{$package_name}}"
@endsection

@section('title')
    <i class="fa fa-archive fa-fw" aria-hidden="true" xmlns:v-bind="http://www.w3.org/1999/xhtml"
       xmlns:v-bind="http://www.w3.org/1999/xhtml"></i>
    @lang('modules.uninstalling') "{{$package_name}}"
@endsection

@section('container')
    @php
        if(!isset($url) || $url=='') $url = url('/');
    @endphp
    @if(!$installed)
        <p><strong>@lang('modules.not_installed')</strong></p>
        @if($url!=URL::current())
            <div class="buttons">
                <a href="{{ $url }}" class="button">@lang('modules.ok')</a>
            </div>
        @endif
    @else
        <!-- This div is rendered by Vue.js: -->
        <div id="app">
            <div id="error_alert" class="alert alert-danger" role="alert" style="display: none;">
                <div id="errors"></div>
            </div>
            <div id="content">
                <div style="height: 95px;">
                    <div v-for="progress in ajax.progress" v-if="progress.view">
                        <div class="progress">
                            <div id="progress" class="progress-bar progress-bar-striped progress-bar-animated"
                                 role="progressbar"
                                 aria-valuenow="0" aria-valuemin="0" aria-valuemax="100"
                                 v-bind:style="{ width: (progress.percent*100) + '%' }"></div>
                        </div>
                        <div class="text-left pull-left">
                            <small v-html="progress.message"></small>
                        </div>
                        <div class="text-right pull-right" id="package">
                            <small>&nbsp;<span id="name"></span> <i id="version"></i></small>
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div>
                <hr>
                <p><strong><small>@lang('modules.console')</small></strong></p>
                <pre id="logs" style="height:130px;">
                    <code>
                        <p v-for="log in logs" v-if="log" v-html="log"></p>
                    </code>
                </pre>

            </div>
            <div id="finish" style="display: none;">
                <p><strong>@lang('modules.uninstalling_completed')</strong></p>
            </div>
        </div>
    @endif

    <!-- Include Vue.js: -->
    <script src="https://unpkg.com/vue/dist/vue.min.js"></script>
    <!-- Our own JavaScript code: -->
    <script type="application/javascript">
        var evtSource = false;
        var app = new Vue({
            el: '#app',
            data: {
                ajax: [],
                logs: [],
                loading: false
            },
            computed: {
                buttonLabel: function () {
                    return (this.loading ? 'Loading…' : 'Go');
                }
            },
            methods: {
                run: function () {

                    this.reset();

                    var streamUrl = '{{route('SunAppModules::uninstall',['package'=>$package,'url'=>$url])}}';

                    evtSource = new EventSource(streamUrl);
                    this.loading = true;

                    var that = this;

                    evtSource.addEventListener('message', function (e) {
                        that.ajax = JSON.parse(e.data);
                        if (typeof that.ajax.log !== 'undefined') {
                            that.logs.push(that.ajax.log);
                            var objDiv = document.getElementById("logs");
                            objDiv.scrollTop = objDiv.scrollHeight;
                            //console.log(that.ajax);
                        }
                    }, false);
                    evtSource.addEventListener('close', function (e) {
                        evtSource.close();
                        $('#content').slideUp();
                        $('#finish').slideDown();
                        @if($url!=URL::current())
                            location.href = '{{$url}}';
                        @endif
                            that.loading = false;
                    }, false);

                    evtSource.addEventListener('error', function (e) {
                        evtSource.close();
                        var message = JSON.parse(e.data).message;
                        $('#content').slideUp();
                        $('#error_alert').slideDown();
                        $('#error_alert #errors').html(message);
                        that.loading = false;
                    }, false);

                },
                reset: function () {
                    if (evtSource !== false) {
                        evtSource.close();
                    }

                    this.loading = false;
                    this.ajax = [];
                    this.logs = [];
                }
            }
        });
        app.run();
    </script>
@endsection
