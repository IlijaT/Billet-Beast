<?php

namespace App\Http\Controllers\Backstage;

use App\Concert;
use Carbon\Carbon;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertsController extends Controller
{

    public function index()
    {
        return view('backstage.concerts.index', ['concerts' => Auth::user()->concerts]);
    }

    public function create()
    {
        return view('backstage.concerts.create');
    }

    public function store()
    {
        request()->validate([
            'title' => 'required',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:ia',
            'ticket_price' => 'required|numeric|min:5',
            'ticket_qunatity' => 'required|numeric|min:1',

        ]);

        $concert = Auth::user()->concerts()->create([
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

        $concert->publish();

        return redirect()->route('concerts.show', ['id' => $concert]);
    }

    public function edit($id)
    {
        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), 403);

        return view('backstage.concerts.edit', [
            'concert' => $concert
        ]);
    }

    public function update($id)
    {

        request()->validate([
            'title' => 'required',
            'venue' => 'required',
            'venue_address' => 'required',
            'city' => 'required',
            'state' => 'required',
            'zip' => 'required',
            'date' => 'required|date',
            'time' => 'required|date_format:g:ia',
            'ticket_price' => 'required|numeric|min:5',
        ]);

        $concert = Auth::user()->concerts()->findOrFail($id);

        abort_if($concert->isPublished(), 403);

        $concert->update([
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
        ]);


        return redirect()->route('backstage.concerts.index');
    }
}
