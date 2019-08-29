<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\OrderConfirmationNumberGenerator;


class OrderConfirmationNumber extends Facade{

  public static function getFacadeAccessor()
  {
    return OrderConfirmationNumberGenerator::class;
  }
  
}