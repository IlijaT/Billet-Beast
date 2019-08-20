<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PublishConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_promoter_can_publish_their_own_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 3
        ]);

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id
        ]);

        $concert = $concert->fresh();
        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue($concert->isPublished());
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function a_concert_can_only_be_published_once()
    {

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => $user->id,
            'ticket_quantity' => 3
        ]);

        $concert->publish();
        $this->assertEquals(3, $concert->ticketsRemaining());

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id
        ]);

        $response->assertStatus(422);
        $this->assertEquals(3, $concert->fresh()->ticketsRemaining());
    }

    /** @test */
    public function a_promoter_cannot_publish_other_concert()
    {

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $concert = factory(Concert::class)->create([
            'user_id' => $otherUser->id,
            'ticket_quantity' => 3
        ]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => $concert->id
        ]);

        $response->assertStatus(404);
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    public function a_guest_cannot_publish_any_concert()
    {

        $concert = factory(Concert::class)->create([
            'ticket_quantity' => 3
        ]);

        $response = $this->post('/backstage/published-concerts', [
            'concert_id' => $concert->id
        ]);

        $response->assertRedirect('/login');
        $concert = $concert->fresh();
        $this->assertFalse($concert->isPublished());
        $this->assertEquals(0, $concert->ticketsRemaining());
    }

    /** @test */
    public function concert_that_do_not_exists_cannot_be_published()
    {

        $user = factory(User::class)->create();

        $response = $this->actingAs($user)->post('/backstage/published-concerts', [
            'concert_id' => 999
        ]);

        $response->assertStatus(404);
    }
}
