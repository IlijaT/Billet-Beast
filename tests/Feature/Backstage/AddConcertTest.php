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

    public function from($url)
    {
        session()->setPreviousUrl(url($url));

        return $this;
    }

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

    /** @test */
    public function guest_cannot_add_a_new_concert()
    {

        $response = $this->post('/backstage/concerts', [
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

        $response->assertStatus(302);
        $response->assertRedirect("/login");
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ttile_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => '',
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

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function venue_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => '8:00pm',
            'venue' => '',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('venue');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function venue_address_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => '8:00pm',
            'venue' => 'The IT Arena',
            'venue_address'  => '',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function city_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => '8:00pm',
            'venue' => 'The IT Arena',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => '',
            'state' => 'Srbija',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('city');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function state_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => '8:00pm',
            'venue' => 'The IT Arena',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => 'Novi Sad',
            'state' => '',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('state');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function zip_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => '8:00pm',
            'venue' => 'The IT Arena',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('zip');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function subtitle_is_optional()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => '',
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
            $this->assertNull($concert->subtitle);
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

    /** @test */
    public function additional_information_is_optional()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => '',
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
            $this->assertNull($concert->additional_information);
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

    /** @test */
    public function date_must_be_a_valid_date()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => 'not a valid date',
            'time'  => '8:00pm',
            'venue' => 'The IT Arena',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function time_must_be_valid_time()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
            'title' => 'No Warning',
            'subtitle' => 'With Cruel Hand',
            'additional_information' => 'Must be 19+',
            'date'  => '2019-10-10',
            'time'  => 'not valid time',
            'venue' => 'The IT Arena',
            'venue_address'  => 'Bulevar Ilije Tatalovica 35',
            'city'  => 'Novi Sad',
            'state' => 'Srbija',
            'zip' => '21000',
            'ticket_price'  => '32.50',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_numeric()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
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
            'ticket_price'  => 'not numeric',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_at_least_5()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
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
            'ticket_price'  => '4.99',
            'ticket_qunatity'  => '75',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_qunatity_must_be_numeric()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
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
            'ticket_qunatity'  => 'not numeric',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_qunatity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_qunatity_must_be_at_least_1()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', [
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
            'ticket_qunatity'  => '0',
        ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_qunatity');
        $this->assertEquals(0, Concert::count());
    }
}
