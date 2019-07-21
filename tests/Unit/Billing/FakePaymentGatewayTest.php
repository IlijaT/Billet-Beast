<?php

namespace Tests\Unit\Billing;

use Tests\TestCase;
use App\Billing\FakePaymentGateway;
use App\Billing\PaymentFailedException;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FakePaymentGatewayTest extends TestCase
{
   /** @test */
   public function charges_with_a_valid_payment_token_are_successful()
   {
     $paymentGateway = new FakePaymentGateway;

     $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());

     $this->assertEquals(2500, $paymentGateway->totalCharges());
   }

   /** @test */
   public function charges_with_invalid_payment_token_fail()
   {
      try {
        $paymentGateway = new FakePaymentGateway;
        $paymentGateway->charge(2500, 'invalid-payment-token');
      } catch(PaymentFailedException $e) {
        $this->assertEquals(0, $paymentGateway->totalCharges());
        return;
      }

      $this->fail();

   }
}
