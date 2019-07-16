<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>

        <!-- Styles -->
        <style>
            html, body {
                background-color: #fff;
                color: #636b6f;
                font-family: 'Nunito', sans-serif;
                font-weight: 200;
                height: 100vh;
                margin: 0;
            }

            .content {
                text-align: center;
            }

            .title {
                font-size: 84px;
            }

        
        </style>
    </head>
    <body>
        
            <div class="content">
                <div class="title m-b-md">
                   {{ $concert->title }}
                </div>
                <h2> {{ $concert->subtitle }}</h2>
                <p>{{ $concert->formatted_date }}</p>
                <p>Doors at {{ $concert->formatted_start_time }}</p>
                <p>{{ number_format($concert->ticket_price / 100, 2) }}</p>
                <p>{{ $concert->venue }}</p>
                <p>{{ $concert->venue_address }}</p>
                <p>{{ $concert->city }}</p>
                <p>{{ $concert->state }}</p>
                <p>{{ $concert->zip }}</p>
                <p>{{ $concert->additional_information }}</p>
            </div>

        </div>
    </body>
</html>
