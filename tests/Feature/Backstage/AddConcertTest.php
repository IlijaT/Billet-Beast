<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AddConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function promoters_can_view_add_concert_form()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get('/backstage/concerts/new');

        $response->assertOk();
    }

    /** @test */
    public function guests_cannot_view_add_concert_form()
    {
        $response = $this->get('/backstage/concerts/new');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function adding_a_valid_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => '8:00pm',
            'venue' => 'The IT Arena',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response) {
            $response->assertStatus(302);

            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertEquals('No Warning', $concert->title);
            $this->assertEquals('With Cruel Hand', $concert->subtitle);
            $this->assertEquals('Must be 19+', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2019-10-10 8:00pm'), $concert->date);
            $this->assertEquals('The IT Arena', $concert->venue);
            $this->assertEquals('Bulevar Ilije Tatalovica 35', $concert->venue_address);
            $this->assertEquals('Novi Sad', $concert->city);
            $this->assertEquals('Srbija', $concert->state);
            $this->assertEquals('21000', $concert->zip);
            $this->assertEquals(3250, $concert->ticket_price);
            $this->assertEquals(75, $concert->ticketsRemaining());
        });
    }
}
