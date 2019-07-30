<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StripePaymentGatewayTest extends TestCase
{

    private function lastCharge()
    {
        return \Stripe\Charge::all(
            ["limit" => 1],
            ['api_key' => config('services.stripe.secret')]
            )['data'][0];
    }

    private function newCharges()
    {
        return \Stripe\Charge::all(
            [
                'limit' => 1,
                'ending_before' => $this->lastCharge->id
            ],
            ['api_key' => config('services.stripe.secret')]
            )['data'];
        
    }

    private function validToken()
    {
       return \Stripe\Token::create([
        'card' => [
          'number' => '4242424242424242',
          'exp_month' => 1,
          'exp_year' => date('Y') + 1,
          'cvc' => '123'
        ]
        ], ['api_key' => config('services.stripe.secret')] )->id; 
    }

    protected function setUp() : void
    {
        parent::setUp();

        $this->lastCharge = $this->lastCharge();
    }

   /** @test */
   public function charges_with_a_valid_payment_token_are_successful()
   {

    // New PaymentGateway instance
    $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));

    // Create a new charge with a paymentGateWay valid token
    $paymentGateway->charge(2500, $this->validToken());

    // verity that the charge was created successfullly


    $this->assertCount(1, $this->newCharges());
    $this->assertEquals(2500, $this->lastCharge()->amount);
   }
}
