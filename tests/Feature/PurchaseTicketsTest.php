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
        $this->assertArrayHasKey('email', $response->decodeResponseJson('errors'));

        
    }

    /** @test */
    public function email_must_be_valid_email_to_purchase_tickets()
    {

        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'not_valid_email',
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $response->assertStatus(422);
        $this->assertArrayHasKey('email', $response->decodeResponseJson('errors'));
        
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'email' => 'john@example.com',
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $response->assertStatus(422);
        $this->assertArrayHasKey('ticket_quantity', $response->decodeResponseJson('errors'));
      
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 0,
            'email' => 'john@example.com',
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $response->assertStatus(422);
        $this->assertArrayHasKey('ticket_quantity', $response->decodeResponseJson('errors'));
    }

    /** @test */
    public function payment_token_is_required()
    {
        $concert = factory(Concert::class)->create();

        $response = $this->json('POST', "/concerts/{$concert->id}/orders", [
            'ticket_quantity' => 3,
            'email' => 'john@example.com',
            ]);
        
        $response->assertStatus(422);
        $this->assertArrayHasKey('payment_token', $response->decodeResponseJson('errors'));
    }
}
