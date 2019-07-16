<?php

namespace Tests\Feature;

use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ExampleTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function a_user_ca_view_concert_listing()
    {
        $this->withoutExceptionHandling();
        // Arrange
        // Create a concert
        $concert = Concert::create([
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

        // Act
        // View the concert listings 
        $response = $this->get('/concerts/'. $concert->id);



        // Assert
        // See the concert details
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
}
