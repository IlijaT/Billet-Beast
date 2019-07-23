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

    private function orderTickets($concert, $params)
    {
        return $this->json('POST', "/concerts/{$concert->id}/orders",  $params);
    }

    private function assertValidationError($response, $field)
    {
        $response->assertStatus(422);
        $this->assertArrayHasKey($field, $response->decodeResponseJson('errors'));
    }
    
	/** @test */
    public function a_customer_can_purchase_tickets_to_a_published_concert()
    {

        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 3250
        ])->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
        ]);
        
        $response->assertStatus(201)
                ->assertJson([
                    'email' => 'john@example.com',
                    'ticket_quantity' => 3,
                    'amount' => 9750
                ]);
                
        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ordersFor('john@example.com')->first()->ticketQuantity());
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert()
    {
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
        ]);
        
        $response->assertStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());

    }

    /** @test */
    public function an_order_is_not_created_if_payments_fails()
    {

        $concert = factory(Concert::class)->states('published')->create()->addTickets(3);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'email' => 'john@example.com',
            'payment_token' => 'invalid-payment-token'
            ]);
        
        $response->assertStatus(422);

        $this->assertFalse($concert->hasOrderFor('john@example.com'));
    }

    /** @test */
    public function cannot_purchase_more_tickets_that_remain()
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
        ]);
        
        $response->assertStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());

    }

    /** @test */
    public function email_is_required_to_purchase_tickets()
    {

        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $this->assertValidationError($response, 'email');

        
    }

    /** @test */
    public function email_must_be_valid_email_to_purchase_tickets()
    {

        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'not_valid_email',
            'ticket_quantity' => 3,
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $this->assertValidationError($response, 'email');
        
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $response = $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $this->assertValidationError($response, 'ticket_quantity');
      
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 0,
            'email' => 'john@example.com',
            'payment_token'=> $this->paymentGateway->getValidTestToken()
            ]);
        
        $this->assertValidationError($response, 'ticket_quantity');
    }

    /** @test */
    public function payment_token_is_required()
    {
        $concert = factory(Concert::class)->states('published')->create();
        $concert->addTickets(3);

        $response = $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'email' => 'john@example.com',
            ]);
        
        $this->assertValidationError($response, 'payment_token');
    }


}
