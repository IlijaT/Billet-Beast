<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway
{

  const TEST_CARD_NUMBER = '4242424242424242';

  private $charges;
  private $tokens;
  private $beforeFirstChargeCallback;

  public function __construct()
  {
    $this->charges = collect();
    $this->tokens = collect();
  } 
  
  public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
  {
    $token = 'fake_tok'.str_random(24);
    $this->tokens[$token] = $cardNumber;

    return $token;
  }

  public function charge($amount, $token)
  {
    if ($this->beforeFirstChargeCallback !== null) {
      $callback = $this->beforeFirstChargeCallback;
      $this->beforeFirstChargeCallback = null;
      $callback->__invoke($this);
    }

    if (! $this->tokens->has($token)) {
      throw new PaymentFailedException();
    }
    
    return $this->charges[] = new Charge([
      'amount' => $amount,
      'card_last_four' => substr($this->tokens[$token], -4) 
    ]);
  }

  public function totalCharges()
  {
     return $this->charges->map->amount()->sum();
  }

  public function beforeFirstCharge($callback)
  {
    $this->beforeFirstChargeCallback = $callback;
  }

  public function newChargesDuring($callback)
  {
      $chargesFrom = $this->charges->count();

      $callback($this);

      return $this->charges->slice($chargesFrom)->reverse()->values();
  }

  private function lastCharge()
  {
      return array_first(\Stripe\Charge::all(
            ["limit" => 1],
            ['api_key' => $this->apiKey]
          )['data']);
  }
}