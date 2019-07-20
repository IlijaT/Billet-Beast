<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Billing\PaymentGateway;
use App\Concert;

class ConcertOrdersController extends Controller
{
    protected $paymentGateway;


    public function __construct(PaymentGateway $paymentGateway)
    {
        $this->paymentGateway = $paymentGateway;
    }

    public function store(Concert $concert)
    {
        request()->validate([
            'email' => 'required'
        ]);

        // Charging customer
        $this->paymentGateway->charge(request('ticket_quantity') * $concert->ticket_price, request('valid_token'));

        // Creating order
        $order = $concert->orderTickets(request('email'),request('ticket_quantity'));

        return response()->json([], 201);
    }
}
