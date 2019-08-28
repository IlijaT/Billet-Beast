@extends('layouts.master')

@section('content')
<div class="full-height bg-soft flex-col">

    <div class="flex">
        @yield('backstageContent')
    </div>

    <footer class="bg-dark p-xs-y-6 text-light-muted">
        <div class="container">
            <p class="text-center">&copy; BilletBeast {{ date('Y') }}</p>
        </div>
    </footer>
</div>
@endsection