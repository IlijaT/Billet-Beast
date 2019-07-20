<?php

namespace Tests\Feature;

use App\Concert;
use Tests\TestCase;
use App\Billing\PaymentGateway;
use App\Billing\FakePaymentGateway;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PurchaseTicketsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp() : void
    {
        parent::setUp();

        $this->paymentGateway = new FakePaymentGateway;
        app()->instance(PaymentGateway::class, $this->paymentGateway);

    }
    
	/** @test */
    public function a_customer_can_purchase_concert_tickets()
    {

        $concert = factory(Concert::class)->create([
            'ticket_price' => 3250
        ]);

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $response->assertStatus(201);
        
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());

        $order = $concert->orders()->where('email', 'john@example.com')->first();
       
        $this->assertNotNull($order);
        $this->assertEquals(3, $order->tickets()->count());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {

        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $response->assertStatus(422);
        
    }
}
