<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Concert;
use App\Order;
use App\Ticket;
use Carbon\Carbon;

class ViewOrderTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function user_can_view_their_order_confirmation()
    {
        $this->withoutExceptionHandling();

        $concert = factory(Concert::class)->create([
            'date' => Carbon::parse('2019-10-20 20:00')
        ]);

        $order = factory(Order::class)->create([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'amount' => 8500,
            'card_last_four' => 1881
        ]);
        $ticketA = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE123'
        ]);
        $ticketB = factory(Ticket::class)->create([
            'concert_id' => $concert->id,
            'order_id' => $order->id,
            'code' => 'TICKETCODE456'
        ]);

        $response = $this->get("/orders/ORDERCONFIRMATION1234");
        
        $response->assertStatus(200);
        $response->assertViewHas('order', $order);
        $response->assertSee('ORDERCONFIRMATION1234');
        $response->assertSee('$85.00');
        $response->assertSee('**** **** **** 1881');
        $response->assertSee('TICKETCODE123');
        $response->assertSee('TICKETCODE456');
        $response->assertSee('With Fake Openers');
        $response->assertSee('The Example Theatre');
        $response->assertSee('Example Street 123');
        $response->assertSee('Fakeville');
        $response->assertSee('Srbija');
        $response->assertSee('21000');
        // $response->assertSee('john@example.com');
        $response->assertSee('2019-10-20 20:00');


    }
}
