<?php

namespace Tests\Feature\Backstage;

use App\Concert;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class EditConcertTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function promoters_can_view_edit_form_for_their_own_unpublished_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);

        $this->assertFalse($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/ediit");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function promoters_can_not_view_edit_form_for_their_own_published_concerts()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->states('published')->create(['user_id' => $user->id]);

        $this->assertTrue($concert->isPublished());

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/ediit");

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

        $response = $this->get("/backstage/$concert->id/ediit");

        // 302 http response - redirect 
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function guest_are_ask_to_login_when_attempting_to_see_edit_form_of_concert_that_does_not_exist()
    {

        $response = $this->get("/backstage/999/ediit");

        // 302 http response - redirect 
        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }
}
