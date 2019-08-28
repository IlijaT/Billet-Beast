<header class="py-3">
  <div class="row flex-nowrap justify-content-between align-items-center">
    <div class="col-4 pt-1">
      <a class="text-muted" href="/">BilletBeast</a>
    </div>
         
    @if(Auth::check())
 
      <div class="col-4 d-flex justify-content-end align-items-center">
        <form class="inline-block" action="{{ route('auth.logout') }}" method="POST">
            <a class="mr-2">{{ Auth::user()->name }}</a>
            {{ csrf_field() }}
            <button class="btn btn-sm btn-outline-secondary" type="submit">Log out</button>
        </form>
      </div>

    @else
      <div class="col-4 d-flex justify-content-end align-items-center">
        <a class="btn btn-sm btn-outline-secondary mr-2" href="/login">Log in</a>
      </div>
    @endif
  </div>
</header>


