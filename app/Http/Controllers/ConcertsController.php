<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Concert;

class ConcertsController extends Controller
{
    public function show(Concert $concert)
    {

        return view('concerts.show', ['concert' => $concert]);
    }
}
