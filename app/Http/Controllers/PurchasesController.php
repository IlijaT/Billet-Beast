<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PurchasesController extends Controller
{
    public function store()
    {
       dd(request()->all());
    }
}
