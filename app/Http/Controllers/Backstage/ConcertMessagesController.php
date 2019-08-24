<?php

namespace App\Http\Controllers\Backstage;

use Illuminate\Http\Request;
use App\Jobs\SendAttendeeMessage;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;

class ConcertMessagesController extends Controller
{
    public function create($concertId)
    {
        $concert = Auth::user()->concerts()->findOrFail($concertId);
        return view('backstage.concert-messages.create', ['concert' => $concert]);
    }

    public function store($concertId)
    {
        $concert = Auth::user()->concerts()->findOrFail($concertId);

        $this->validate(request(), [
            'subject' => 'required',
            'message' => 'required'
        ]);

        $message = $concert->attendeeMessages()->create(request(['subject', 'message']));

        SendAttendeeMessage::dispatch($message);

        return redirect()->route('backstage.concert-messages.create', ['concert' => $concert])
            ->with('flash', 'Your message has been sent.');
    }
}
