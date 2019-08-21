<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewConcertListingTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function guest_cannot_view_promoters_concert_listing()
    {
        $response = $this->get('/backstage/concerts');

        $response->assertStatus(302);
        $response->assertRedirect('/login');
    }

    /** @test */
    public function promoters_can_only_view_a_list_of_their_own_concerts()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $otherUser = factory(User::class)->create();

        $publishedConcertA = factory(Concert::class)->state('published')->create(['user_id' => $user->id]);
        $publishedConcertB = factory(Concert::class)->state('published')->create(['user_id' => $otherUser->id]);
        $publishedConcertC = factory(Concert::class)->state('published')->create(['user_id' => $user->id]);

        $unpublishedConcertA = factory(Concert::class)->state('unpublished')->create(['user_id' => $user->id]);
        $unpublishedConcertB = factory(Concert::class)->state('unpublished')->create(['user_id' => $otherUser->id]);
        $unpublishedConcertC = factory(Concert::class)->state('unpublished')->create(['user_id' => $user->id]);


        $response = $this->actingAs($user)->get('/backstage/concerts');

        $response->assertStatus(200);

        $response->data('publishedConcerts')->assertContains($publishedConcertA);
        $response->data('publishedConcerts')->assertNotContains($publishedConcertB);
        $response->data('publishedConcerts')->assertContains($publishedConcertC);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertA);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertB);
        $response->data('publishedConcerts')->assertNotContains($unpublishedConcertC);

        $response->data('unpublishedConcerts')->assertContains($unpublishedConcertA);
        $response->data('unpublishedConcerts')->assertNotContains($unpublishedConcertB);
        $response->data('unpublishedConcerts')->assertContains($unpublishedConcertC);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertA);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertB);
        $response->data('unpublishedConcerts')->assertNotContains($publishedConcertC);
    }
}
