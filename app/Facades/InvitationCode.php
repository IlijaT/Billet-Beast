<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;
use App\InvitationCodeGenerator;


class InvitationCode extends Facade
{

  public static function getFacadeAccessor()
  {
    return InvitationCodeGenerator::class;
  }
}
