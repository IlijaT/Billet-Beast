@extends('layouts.master')

@section('content')

	<div class="bg-soft p-xs-y-7 full-height">
		<div class="container">
			<div class="constrain-xl m-xs-auto">
				<div class="m-xs-b-6">
					<div class="d-flex justify-content-between p-xs-y-4 border-b">
						<h1 class="tex-xl">Order Summary</h1>
					<a href="{{ url("/orders/{$order->confirmation_number}") }}" class="link-brand-soft">{{ $order->confirmation_number }}</a>
					</div>
					<div class="p-xs-y-4 border-b">
						<p>
							<strong>Order Total: ${{ number_format($order->amount / 100, 2) }}</strong>
						</p>
						<p class="text-dark-soft">Billed to Card #: **** **** **** {{ $order->card_last_four }}</p>
					</div>
				</div>
				<div class="m-xs-b-7">
					<h2 class="text-lg wt-normal m-xs-b-4">Your Tickets</h2>
					@foreach ($order->tickets as $ticket)						
						<div class="card mb-5">
							<div class="card-section p-2 p-xs-y-3 d-flex justify-content-between align-items-end text-light bg-dark">
								<div>
									<h1 class="text-xl wt-normal">{{ $ticket->concert->title }}</h1>
									<p class="text-light-muted">{{ $ticket->concert->subtitle }}</p>
								</div>
								<div class="text-right">
									<strong>General Admission</strong>
									<p class="text-light-soft">Admit One</p>
								</div>
							</div>
							<div class="card-section border-b p-2">
								<div class="row">
									<div class="col-sm">
										<div class="media-object">
											<div class="media-left">
												<i class="far fa-calendar-alt"></i>
											</div>
											<div class="media-body p-xs-l-4">
												<p class="wt-bold">
													<time datetime="{{ $ticket->concert->date->format('Y-m-d H:i') }}">
														{{ $ticket->concert->formatted_date_with_day }}
													</time>
												</p>
												<p class="text-dark-soft">Doors at {{ $ticket->concert->formatted_start_time }}</p>
											</div>
										</div>
									</div>
									<div class="col-sm">
										<div class="media-object">
											<div class="media-left">
												<i class="fas fa-location-arrow"></i>
											</div>
											<div class="media-body p-xs-l-4">
												<p class="wt-bold">{{ $ticket->concert->venue }}</p>
												<div class="text-dark-soft">
													<p>{{ $ticket->concert->venue_address }}</p>
													<p>{{ $ticket->concert->city }}, {{ $ticket->concert->state }} {{ $ticket->concert->zip }}</p>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="card-section d-flex justify-content-between p-2">
								<p class="text-lg">{{ $ticket->code }}</p>
								<p>{{ $ticket->order->email }}</p>
							</div>
						</div>
					@endforeach
				</div>
				<div class="text-center text-dark-soft wt-medium">
					<p>Powered by BilletBeast</p>
				</div>
			</div>
		</div>
	</div>
	
@endsection