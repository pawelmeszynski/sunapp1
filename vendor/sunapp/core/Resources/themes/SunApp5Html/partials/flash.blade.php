@if (session()->has('flash.message'))
    <div class="alert @switch(session('flash.type')) @case('error') alert-danger @break @case('info') alert-info @break @case('success') alert-success @break @default alert-primary @endswitch alert-dismissible fade show" role="alert">
        <p class="mb-0">
            {{ session('flash.message') }}
        </p>
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true"><i class="feather icon-x-circle"></i></span>
        </button>
    </div>
@endif
