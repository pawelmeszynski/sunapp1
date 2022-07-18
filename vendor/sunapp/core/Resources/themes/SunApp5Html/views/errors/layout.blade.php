@layout('error')
@set('title',$__env->yieldContent('title'))

<section class="row flexbox-container">
    <div class="col-xl-7 col-md-8 col-12 d-flex justify-content-center">
        <div class="card auth-card bg-transparent shadow-none rounded-0 mb-0 w-100">
            <div class="card-content">
                <div class="card-body text-center">
                    <img src="@asset('../app-assets/images/pages/'.$__env->yieldContent('code').'.png')" class="img-fluid align-self-center" alt="branding logo">
                    <h1 class="font-large-2 my-1">@yield('code') - @yield('message')</h1>
                    <p class="p-2">
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
