<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    private function oldAttributes($overrides = [])
    {
        return array_merge([
            'title' => 'Old Title',
            'subtitle' => 'Old Subtitle',
            'additional_information' => 'Old additional information',
            'date' => Carbon::parse('2020-01-01 8:00pm'),
            'venue' => 'Old Venue',
            'venue_address' => 'Old Venue Address',
            'city' => 'Old City',
            'state' => 'Old State',
            'zip' => '00000',
            'ticket_price' => 2000,
            'ticket_quantity' => 5,
        ], $overrides);
    }

    private function validParams($overrides = [])
    {
        return array_merge([
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information',
            'date'  => '2020-12-12',
            'time'  => '8:00pm',
            'venue' => 'New Venue',
            'venue_address'  => 'New Venue Address',
            'city'  => 'New City',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price'  => '72.50',
            'ticket_quantity' => 10,
        ], $overrides);
    }

    /** @test */
    public function promoters_can_view_edit_form_for_their_own_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function promoters_can_not_view_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/edit");

        // 403 http response forbiden
        $response->assertStatus(403);
    }

    /** @test */
    public function promoters_can_not_view_edit_form_for_other_concerts()
    {
        $user = factory(User::class)->create();
        $user2 = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user2->id]);

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/ediit");

        // 404 http response - not found 
        // to not leak information wether concert exist or not
        $response->assertStatus(404);
    }

    /** @test */
    public function promoters_viw_404_when_trying_to_se_edit_form_of_concert_that_does_not_exist()
    {
        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->get("/backstage/concerts/999/ediit");

        // 404 http response - not found 
        $response->assertStatus(404);
    }

    /** @test */
    public function guest_are_ask_to_login_when_attempting_to_see_any_edit_concert_form()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $response = $this->get("/backstage/concerts/$concert->id/edit");

        // 302 http response - redirect 
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_are_ask_to_login_when_attempting_to_see_edit_form_of_concert_that_does_not_exist()
    {

        $response = $this->get("/backstage/concerts/999/edit");

        // 302 http response - redirect 
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_edit_their_own_unpublished_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user->id]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information',
            'date'  => '2020-12-12',
            'time'  => '8:00pm',
            'venue' => 'New Venue',
            'venue_address'  => 'New Venue Address',
            'city'  => 'New City',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price'  => '72.50',
            'ticket_quantity' => 10,
        ]);

        $response->assertRedirect('backstage/concerts');

        tap($concert->fresh(), function ($concert) {
            $this->assertEquals('New Title', $concert->title);
            $this->assertEquals('New Subtitle', $concert->subtitle);
            $this->assertEquals('New additional information', $concert->additional_information);
            $this->assertEquals(Carbon::parse('2020-12-12 8:00pm'), $concert->date);
            $this->assertEquals('New Venue', $concert->venue);
            $this->assertEquals('New Venue Address', $concert->venue_address);
            $this->assertEquals('New City', $concert->city);
            $this->assertEquals('New State', $concert->state);
            $this->assertEquals('99999', $concert->zip);
            $this->assertEquals(7250, $concert->ticket_price);
            $this->assertEquals(10, $concert->ticket_quantity);
        });
    }

    /** @test */
    public function promoters_cannot_edit_other_promoters_unpublished_concerts()
    {

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $otherUser->id]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information',
            'date'  => '2020-12-12',
            'time'  => '8:00pm',
            'venue' => 'New Venue',
            'venue_address'  => 'New Venue Address',
            'city'  => 'New City',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price'  => '72.50',
            'ticket_qunatity'  => '10',
        ]);

        $response->assertStatus(404);

        $this->assertArraySubset($this->oldAttributes(['user_id' => $otherUser->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    public function promoters_cannot_edit_published_concerts()
    {

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->states('published')->create($this->oldAttributes(['user_id' => $user]));

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information',
            'date'  => '2020-12-12',
            'time'  => '8:00pm',
            'venue' => 'New Venue',
            'venue_address'  => 'New Venue Address',
            'city'  => 'New City',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price'  => '72.50',
            'ticket_qunatity'  => '10',
        ]);

        $response->assertStatus(403);
        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    public function guests_cannot_edit_concerts()
    {

        $user = factory(User::class)->create();

        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user->id]));

        $this->assertFalse($concert->isPublished());

        $response = $this->patch("/backstage/concerts/{$concert->id}", [
            'title' => 'New Title',
            'subtitle' => 'New Subtitle',
            'additional_information' => 'New additional information',
            'date'  => '2020-12-12',
            'time'  => '8:00pm',
            'venue' => 'New Venue',
            'venue_address'  => 'New Venue Address',
            'city'  => 'New City',
            'state' => 'New State',
            'zip' => '99999',
            'ticket_price'  => '72.50',
        ]);

        $response->assertRedirect('/login');
        $this->assertArraySubset($this->oldAttributes(['user_id' => $user->id]), $concert->fresh()->getAttributes());
    }

    /** @test */
    public function title_is_required()
    {

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->from("/backstage/concerts/{$concert->id}/edit")
            ->patch("/backstage/concerts/{$concert->id}", $this->validParams(['title' => '']));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('title');
        $this->assertEquals('Old Title', $concert->fresh()->title);
    }

    /** @test */
    public function subtitle_is_optional()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $this->assertFalse($concert->isPublished());
        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['subtitle' => '']));

        $response->assertRedirect("backstage/concerts/");
        $this->assertEquals('', $concert->fresh()->subtitle);
    }

    /** @test */
    public function aditional_information_is_optional()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['additional_information' => '']));

        $this->assertEquals('', $concert->fresh()->additional_information);
    }

    /** @test */
    public function date_must_be_a_valid_date()
    {

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));
        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['date' => 'not a valid date']));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('date');
        $this->assertEquals(Carbon::parse('2020-01-01 8:00pm'), $concert->fresh()->date);
    }

    /** @test */
    public function time_must_be_valid_time()
    {

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['time' => 'not a valid time']));

        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('time');
        $this->assertEquals(Carbon::parse('2020-01-01 8:00pm'), $concert->fresh()->date);
    }

    /** @test */
    public function venue_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['venue' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue');
        $this->assertEquals('Old Venue', $concert->fresh()->venue);
    }

    /** @test */
    public function venue_address_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['venue_address' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('venue_address');
        $this->assertEquals('Old Venue Address', $concert->fresh()->venue_address);
    }

    /** @test */
    public function state_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['state' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('state');
        $this->assertEquals('Old State', $concert->fresh()->state);
    }

    /** @test */
    public function zip_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['zip' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('zip');
        $this->assertEquals('00000', $concert->fresh()->zip);
    }

    /** @test */
    public function ticket_price_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_price' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(2000, $concert->fresh()->ticket_price);
    }

    /** @test */
    public function ticket_price_must_be_numeric()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_price'  => 'not numeric']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(2000, $concert->fresh()->ticket_price);
    }

    /** @test */
    public function ticket_price_must_be_at_least_5()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_price'  => '4.99']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_price');
        $this->assertEquals(2000, $concert->fresh()->ticket_price);
    }

    /** @test */
    public function ticket_quantity_price_is_required()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity' => '']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(5, $concert->fresh()->ticket_quantity);
    }

    /** @test */
    public function ticket_quantity_must_be_integer()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity'  => '7.8']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(5, $concert->fresh()->ticket_quantity);
    }

    /** @test */
    public function ticket_quantity_must_be_more_than_1()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create($this->oldAttributes(['user_id' => $user]));

        $response = $this->actingAs($user)
            ->from("backstage/concerts/{$concert->id}/edit")
            ->patch("backstage/concerts/{$concert->id}", $this->validParams(['ticket_quantity'  => '0']));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/edit");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(5, $concert->fresh()->ticket_quantity);
    }
}
