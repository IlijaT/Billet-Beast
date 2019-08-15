<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{
    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store()
    {
        request()->validate([
            'title' => 'required'
        ]);

        $concert = Concert::create([
            'title' => request('title'),
            'subtitle' => request('subtitle'),
            'additional_information' => request('additional_information'),
            'date'  => Carbon::parse(vsprintf('%s %s', [
                request('date'),
                request('time')
            ])),
            'ticket_price'  => request('ticket_price') * 100,
            'venue' => request('venue'),
            'venue_address'  => request('venue_address'),
            'city'  => request('city'),
            'state' => request('state'),
            'zip' => request('zip'),
        ])->addTickets(request('ticket_qunatity'));

        return redirect()->route('concerts.show', ['id' => $concert]);
    }
}
