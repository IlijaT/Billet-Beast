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

    private function validParams($overrides = [])
    {
        return array_merge([
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
        ], $overrides);
    }

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

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);

            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertTrue($concert->user->is($user));

            $this->assertTrue($concert->isPublished());

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

        $response = $this->post('/backstage/concerts', $this->validParams());

        $response->assertStatus(302);
        $response->assertRedirect("/login");
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ttile_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams(['title' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('title');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function venue_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'venue' => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('venue');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function venue_address_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'venue_address'  => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function city_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'city'  => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('city');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function state_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'state'  => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('state');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function zip_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'zip'  => '',
        ]));

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

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'subtitle'  => '',
        ]));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);

            $this->assertTrue($concert->user->is($user));

            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertNull($concert->subtitle);
        });
    }

    /** @test */
    public function additional_information_is_optional()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'additional_information'  => '',
        ]));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);

            $this->assertTrue($concert->user->is($user));

            $response->assertRedirect("/concerts/{$concert->id}");

            $this->assertNull($concert->additional_information);
        });
    }

    /** @test */
    public function date_must_be_a_valid_date()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'date'  => 'not a valid date',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('date');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function time_must_be_valid_time()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'time'  => 'not a valid time',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('time');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_price'  => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_numeric()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_price'  => 'not numeric',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_price_must_be_at_least_5()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_price'  => '4.99',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_qunatity_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_qunatity'  => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_qunatity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_qunatity_must_be_numeric()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_qunatity'  => 'not numeric',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_qunatity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_qunatity_must_be_at_least_1()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_qunatity'  => '0',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_qunatity');
        $this->assertEquals(0, Concert::count());
    }
}
