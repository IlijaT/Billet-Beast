<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListingTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function a_user_can_view_a_published_concert_listing()
    {

        $concert = factory(Concert::class)->states('published')->create([
            'title' => 'The Red Chord',
            'subtitle' => 'With Animosity and Lethargy',
            'date'  => Carbon::parse('August 13th, 2019, 8:00pm'),
            'ticket_price'  => 550,
            'venue' => 'Spens - Mala sala', 
            'venue_address'  => 'Maksima Gorkog 4',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '21000',
            'additional_information' => 'For tickets call +381642233444',
        ]);

        $response = $this->get('/concerts/'. $concert->id);

        $response->assertSee('The Red Chord');
        $response->assertSee('With Animosity and Lethargy');
        $response->assertSee('August 13, 2019');
        $response->assertSee('8:00pm');
        $response->assertSee('5.50');
        $response->assertSee('Spens - Mala sala');
        $response->assertSee('Maksima Gorkog 4');
        $response->assertSee('Novi Sad');
        $response->assertSee('Srbija');
        $response->assertSee('21000');
        $response->assertSee('For tickets call +381642233444');

    }

    /** @test */
    public function a_user_cannot_view_unpublished_concert_listing()
    {
        
        $concert = factory(Concert::class)->states('unpublished')->create();

        $response = $this->get('/concerts/'. $concert->id);

        $response->assertStatus(404);

    }
}
