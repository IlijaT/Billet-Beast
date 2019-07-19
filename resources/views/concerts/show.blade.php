@extends('layouts.master')

@section('content')

    <div class="bg-soft p-xs-y-7 full-height">
        <div class="container">
            <div class="row m-xs-b-5">
                <div class="col col-md-6 col-md-offset-3 m-xs-b-4 m-lg-b-0">
                    <div class="card">
                        <div class="card-section">
                            <div class="m-xs-b-5">
                                <h1 class="wt-bold text-ellipsis">{{ $concert->title }}</h1>
                                <span class="wt-medium text-ellipsis">{{ $concert->subtitle }}</span>
                            </div>

                            <div class="m-xs-b-5">
                                <div class="media-object">
                                    <div class="media-left">
                                        <i class="far fa-calendar-alt"></i>
                                    </div>
                                    <div class="media-body p-xs-l-2">
                                        <span class="wt-medium">{{ $concert->formatted_date }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="m-xs-b-5">
                                <div class="media-object">
                                    <div class="media-left">
                                        <i class="far fa-clock"></i>
                                    </div>
                                    <div class="media-body p-xs-l-2">
                                        <span class="wt-medium block">Doors at {{ $concert->formatted_start_time }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="m-xs-b-5">
                                <div class="media-object">
                                    <div class="media-left">
                                        <i class="fas fa-dollar-sign"></i>
                                    </div>
                                    <div class="media-body p-xs-l-2">
                                        <span class="wt-medium block">{{ $concert->ticket_price_in_dollars }}</span>
                                    </div>
                                </div>
                            </div>

                            <div class="text-dark-soft m-xs-b-5">
                                <div class="media-object">
                                    <div class="media-left">
                                        <i class="fas fa-map-marker-alt"></i>
                                    </div>
                                    <div class="media-body p-xs-l-2">
                                        <h3 class="wt-medium text-dark text-base">{{ $concert->venue }}</h3>
                                        {{ $concert->venue_address }} <br>
                                        {{ $concert->city }} {{ $concert->state }} {{ $concert->zip }}
                                    </div>
                                </div>
                            </div>

                            <div class="text-dark-soft">
                                <div class="media-object">
                                    <div class="media-left">
                                        <i class="fas fa-info"></i>
                                    </div>
                                    <div class="media-body p-xs-l-2">
                                        <h3 class="wt-medium text-dark text-base">Additional information</h3>
                                        <p>{{ $concert->additional_information }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>

@endsection()
