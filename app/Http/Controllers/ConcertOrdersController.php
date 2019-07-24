<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Billing\PaymentFailedException;
use App\Exceptions\NotEnoughTicketsException;

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
            $tickets = $concert->findTickets(request('ticket_quantity'));
            // Charging the customer for the tickets
            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));
            // Creating order
            $order = $concert->createOrder(request('email'), $tickets);

            return response()->json($order, 201);

        } catch (PaymentFailedException $e) {
            return response()->json([], 422);

        } catch (NotEnoughTicketsException $e) {

            return response()->json([], 422);

        }
    }
    
}
