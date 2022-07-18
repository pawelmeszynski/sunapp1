@layout(false)
<div class="user-nav d-md-flex d-none">
    <span class="user-name">{{$user->name}}</span>
    <span class="user-status">{{$user->email}}</span>
</div>
<span><img class="round" src="@asset('images/user-icon.jpg')" alt="avatar" height="40" width="40"/></span>

<form id="logout-form" action="{{ route('SunApp::logout') }}" method="POST" style="display: none;">
    @csrf
</form>
