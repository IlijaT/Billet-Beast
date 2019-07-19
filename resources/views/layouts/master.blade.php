<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <!-- CSRF Token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'BilletBeast') }}: @yield('title')</title>

    <!-- Scripts -->
    @include('scripts.app')
    
    

    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:400,700" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.7.2/css/all.css" integrity="sha384-fnmOCqbTlWIlj8LyTjo7mOUStjsKC4pOpQbqyi7RrhN7udi9RwhKkMHpvLbHG9Sr" crossorigin="anonymous">

    <!-- Styles -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
</head>

  <body class="container">

    <div id="app" >
      </main> 
        @include('layouts.nav')

        <div>

          @yield('content')

        </div> 

      </main> 

    </div> 
    @include('layouts.footer')
    @stack('beforeScripts')
      <script src="{{ asset('js/app.js') }}" defer></script>
    @stack('afterScripts')
  </body>
</html>
