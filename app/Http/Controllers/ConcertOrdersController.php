<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Billing\PaymentFailedException;

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
            // Charging customer
            $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('payment_token'));
            // Creating order
            $order = $concert->orderTickets(request('email'),request('ticket_quantity'));
    
            return response()->json([], 201);

        } catch (PaymentFailedException $e) {

            return response()->json([], 422);

        }
    }
    
}
