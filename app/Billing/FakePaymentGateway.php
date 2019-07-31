<?php

namespace App\Billing;

class FakePaymentGateway implements PaymentGateway{

  private $charges;
  private $beforeFirstChargeCallback;

  public function __construct()
  {
    $this->charges = collect();
  } 
  
  public function getValidTestToken()
  {
    return 'valid-token';
  }

  public function charge($amount, $token)
  {
    if ($this->beforeFirstChargeCallback !== null) {
      $callback = $this->beforeFirstChargeCallback;
      $this->beforeFirstChargeCallback = null;
      $callback->__invoke($this);
    }

    if ($token !== $this->getValidTestToken()) {
      throw new PaymentFailedException();
    }

    $this->charges[] = $amount;
  }

  public function totalCharges()
  {
     return $this->charges->sum();
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