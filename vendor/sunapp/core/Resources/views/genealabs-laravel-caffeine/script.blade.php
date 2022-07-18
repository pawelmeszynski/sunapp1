@if (function_exists('csp_nonce'))
    <script nonce="{{ csp_nonce() }}">
@else
    <script>
@endif
@if(app()->isProduction())
var lastCheck=new Date,caffeineSendDrip=function(){var e=window.XMLHttpRequest?new XMLHttpRequest:new ActiveXObject("Microsoft.XMLHTTP");e.onreadystatechange=function(){4===e.readyState&&204===e.status&&(lastCheck=new Date)},e.open("GET","{{ $url }}"),e.setRequestHeader("X-Requested-With","XMLHttpRequest"),e.send()};setInterval(function(){caffeineSendDrip()},{{$interval}}),0<{{$ageCheckInterval}}&&(setInterval(function(){new Date-lastCheck>={{$ageCheckInterval+$ageThreshold}}&&location.reload(!0)},{{$ageCheckInterval}}),window.addEventListener("focus",function(){new Date-lastCheck>={{$ageCheckInterval+$ageThreshold}}&&location.reload(!0)}));
@else
var lastCheck = new Date();
var caffeineSendDrip = function () {
    var xhr = window.XMLHttpRequest
    ? new XMLHttpRequest
    : new ActiveXObject('Microsoft.XMLHTTP');

    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 204) {
            lastCheck = new Date();
        }
    };
    xhr.open('GET', '{{ $url }}');
    xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
    xhr.send();
};
setInterval(function () {
    caffeineSendDrip();
}, {{ $interval }});
if ({{ $ageCheckInterval }} > 0) {
    setInterval(function () {
        if (new Date() - lastCheck >= {{ $ageCheckInterval + $ageThreshold }}) {
            location.reload(true);
        }
    }, {{ $ageCheckInterval }});
    window.addEventListener('focus', function() {
        if (new Date() - lastCheck >= {{ $ageCheckInterval + $ageThreshold }}) {
            location.reload(true);
        }
    });
}
@endif
</script>
