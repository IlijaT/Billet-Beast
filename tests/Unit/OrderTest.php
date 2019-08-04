<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Tests\TestCase;
use App\Reservation;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use App\Billing\Charge;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function creating_an_order_from_tickets_email_and_charge()
    {

        $tickets = factory(Ticket::class, 3)->create();
        $charge = new Charge(['amount' => 3600, 'card_last_four' => '1234']);

        $order = Order::forTickets($tickets, 'john@example.com', $charge);

        $this->assertEquals('john@example.com', $order->email);
        $this->assertEquals(3, $order->ticketQuantity());
        $this->assertEquals(3600, $order->amount);
        $this->assertEquals('1234', $order->card_last_four);

    }

    /** @test */
    public function retrieving_an_order_by_confirmation_number()
    {
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $foundOrder = Order::findByConfirmationNumber('ORDERCONFIRMATION1234');

        $this->assertEquals($order->id, $foundOrder->id);
    }

    /** @test */
    public function retrieving_a_nonexisting_order_by_confirmation_number_throws_exception()
    {
       try {
            Order::findByConfirmationNumber('NONEXISTINGCONFIRMATIONNUMBER');
       } catch(ModelNotFoundException $e) {
            return;
       }

       $this->fail('No matching Order was found for the specific confirmation number, but 
       exception has not been thrown!');
 
    }

    /** @test */
    public function converting_to_an_array()
    {
     
        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'amount' => 6000
        ]);

        $order->tickets()->saveMany(factory(Ticket::class, 5)->create());

        $result = $order->toArray();

        $this->assertEquals([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'jane@example.com',
            'ticket_quantity' => 5,
            'amount' => 6000
        ], $result);
    }

}
