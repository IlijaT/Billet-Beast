<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use App\Events\ConcertAdded;
use Illuminate\Http\Testing\File;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Storage;
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
            'ticket_quantity'  => '75',
        ], $overrides);
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
            'ticket_quantity'  => '75',
        ]);

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
            $response->assertRedirect("/backstage/concerts");
            $this->assertTrue($concert->user->is($user));
            $this->assertFalse($concert->isPublished());

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
            $this->assertEquals(75, $concert->ticket_quantity);
            $this->assertEquals(0, $concert->ticketsRemaining());
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
    public function title_is_required()
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
            $response->assertRedirect("/backstage/concerts");
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
            $response->assertRedirect("/backstage/concerts");
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
    public function ticket_quantity_is_required()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_quantity'  => '',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_quantity_must_be_numeric()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_quantity'  => 'not numeric',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'ticket_quantity'  => '0',
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('ticket_quantity');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function poster_image_is_uploaded_if_included()
    {

        Event::fake(ConcertAdded::class);
        Storage::fake('public');

        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 850, 1100);

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'poster_image'  => $file,
        ]));

        tap(Concert::first(), function ($concert) use ($file) {
            $this->assertNotNull($concert->poster_image_path);
            Storage::disk('public')->assertExists($concert->poster_image_path);
            $this->assertFileEquals(
                $file->getPathname(),
                Storage::disk('public')->path($concert->poster_image_path)
            );
        });
    }

    /** @test */
    public function poster_image_must_be_an_image()
    {
        Storage::fake('public');

        $user = factory(User::class)->create();
        $file = File::create('not-a-poster.pdf');

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'poster_image'  => $file,
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function poster_image_must_be_at_least_400px_wide()
    {
        Storage::fake('public');

        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 399, 516);

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'poster_image'  => $file,
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function poster_image_must_have_letter_aspect_ratio()
    {
        Storage::fake('public');

        $user = factory(User::class)->create();
        $file = File::image('concert-poster.png', 851, 1100);

        $response = $this->actingAs($user)->from('/backstage/concerts/new')->post('/backstage/concerts', $this->validParams([
            'poster_image'  => $file,
        ]));

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/new");
        $response->assertSessionHasErrors('poster_image');
        $this->assertEquals(0, Concert::count());
    }

    /** @test */
    public function poster_image_is_optional()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/concerts', $this->validParams([
            'poster_image'  => null,
        ]));

        tap(Concert::first(), function ($concert) use ($response, $user) {
            $response->assertStatus(302);
            $this->assertTrue($concert->user->is($user));
            $response->assertRedirect("/backstage/concerts");
            $this->assertNull($concert->poster_image_path);
        });
    }

    /** @test */
    public function an_event_is_fired_when_concert_is_added()
    {

        Event::fake([ConcertAdded::class]);

        $user = factory(User::class)->create();

        $this->actingAs($user)->post('/backstage/concerts', $this->validParams());

        Event::assertDispatched(ConcertAdded::class, function ($event) {
            $concert = Concert::firstOrFail();
            return $event->concert->is($concert);
        });
    }
}
