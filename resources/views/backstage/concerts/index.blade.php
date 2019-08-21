@extends('layouts.backstage')

@section('backstageContent')
    <div class="bg-light py-4 border-b">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <h1 class="text-lg">Your concerts</h1>
                <a href="{{ route('backstage.concerts.new') }}" class="btn btn-primary">Add concert</a>
            </div>
        </div>
    </div>
    <div class="bg-soft py-5">
        <div class="container mb-4">
            <div class="mb-6">
                <h2 class="mb-3 text-base wt-medium text-dark-soft">Published</h2>
                <div class="row">
                    @foreach ($publishedConcerts as $concert)
                    <div class="col-12 col-lg-4">
                        <div class="card mb-4 p-2 shadow rounded">
                            <div class="card-section">
                                <div class="mb-4">
                                    <div class="mb-2">
                                        <h1 class="text-lg wt-bold">{{ $concert->title }}</h1>
                                        <p class="wt-medium text-dark-soft text-ellipsis">{{ $concert->subtitle }}</p>
                                    </div>
                                    <p class="text-sm mb-2">
                                        {{ $concert->venue }} &ndash; {{ $concert->city }}, {{ $concert->state }}
                                    </p>
                                    <p class="text-sm">
                                        {{ $concert->formatted_date }} @ {{ $concert->formatted_start_time }}
                                    </p>
                                </div>
                                <div>
                                    <a href="{{ route('backstage.published-concert-orders.index', $concert) }}" class="btn btn-sm btn-secondary mr-2">Manage</a>
                                    <a href="{{ route('concerts.show', $concert) }}" class="link-brand text-sm wt-medium">Public Link</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
            <div>
                <h2 class="mb-3 text-base wt-medium text-dark-soft">Drafts</h2>
                <div class="row">
                    @foreach ($unpublishedConcerts as $concert)
                    <div class="col-12 col-lg-4">
                        <div class="card mb-4 p-2 shadow rounded">
                            <div class="card-section">
                                <div class="mb-4">
                                    <div class="mb-2">
                                        <h1 class="text-lg wt-bold">{{ $concert->title }}</h1>
                                        <p class="wt-medium text-dark-soft text-ellipsis">{{ $concert->subtitle }}</p>
                                    </div>
                                    <p class="text-sm mb-2">
                                        {{ $concert->venue }} &ndash; {{ $concert->city }}, {{ $concert->state }}
                                    </p>
                                    <p class="text-sm">
                                        {{ $concert->formatted_date }} @ {{ $concert->formatted_start_time }}
                                    </p>
                                </div>
                                <div class="d-flex">
                                    <a href="{{ route('backstage.concerts.edit', $concert) }}" class="btn btn-sm btn-secondary mr-2">Edit</a>
                                    <form class="inline-block" action="{{ route('backstage.published-concerts.store') }}" method="POST">
                                        {{ csrf_field() }}
                                        <input type="hidden" name="concert_id" value="{{ $concert->id }}">
                                        <button type="submit" class="btn btn-sm btn-primary">Publish</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endsection