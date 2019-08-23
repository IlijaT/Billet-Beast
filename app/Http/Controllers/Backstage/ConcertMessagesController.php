<?php

namespace App\Http\Controllers\Backstage;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create($concertId)
    {
        $concert = Auth::user()->concerts()->published()->findOrFail($concertId);

        return view('backstage.concert-messages.create', [
            'concert' => $concert
        ]);
    }
}
