<?php

namespace App\Http\Controllers;

use App\Order;
use App\Concert;
use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;
use App\Reservation;

class ConcertOrdersController extends Controller
{
    protected $paymentGateway;


    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store( $concert_id)
    {
       
        $concert = Concert::published()->findOrFail($concert_id);
        
        request()->validate([
            'email' => 'required|email',
            'ticket_quantity' => 'required|integer|min:1',
            'payment_token' => 'required',
        ]);

        try {
            // Find some tickets 
            $reservation = $concert->reserveTickets(request('ticket_quantity'));

            // Charging the customer for the tickets
            $this->paymentGateway->charge($reservation->totalCost(), request('payment_token'));

            // Creating order
            $order = Order::forTickets($reservation->tickets(), request('email'), $reservation->totalCost());

            return response()->json($order, 201);

        } catch (PaymentFailedException $e) {
            $reservation->cancel();
            return response()->json([], 422);

        } catch (NotEnoughTicketsException $e) {

            return response()->json([], 422);

        }
    }
    
}
