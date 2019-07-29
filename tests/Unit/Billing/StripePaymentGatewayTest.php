<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class StripePaymentGatewayTest extends TestCase
{

   /** @test */
   public function charges_with_a_valid_payment_token_are_successful()
   {
    // New PaymentGateway instance
    $paymentGateway = new StripePaymentGateway('sk_test_4eC39HqLyjWDarjtT1zdp7dc');
    // api_key treba da se dobija iz config('services.stripe.secret')

    $token = \Stripe\Token::create([
        'card' => [
          'number' => '4242424242424242',
          'exp_month' => 1,
          'exp_year' => date('Y') + 1,
          'cvc' => '123'
        ]
        ], ['api_key' => "sk_test_4eC39HqLyjWDarjtT1zdp7dc"] )->id;

    // Create a new charge with a paymentGateWay valid token
    $paymentGateway->charge(2500, $token);

    // verity that the charge was created successfullly

    $lastCharge = \Stripe\Charge::all(
        ["limit" => 1],
        ['api_key' => "sk_test_4eC39HqLyjWDarjtT1zdp7dc"]
        )['data'][0];

    $this->assertEquals(2500, $lastCharge->amount);
   }
}
