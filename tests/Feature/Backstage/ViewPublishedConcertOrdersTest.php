<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use App\OrderFactory;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ViewPublishedConcertOrdersTest extends TestCase
{
    use RefreshDatabase;


    /** @test */
    public function a_promoter_can_view_the_orders_of_their_own_published_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $order = OrderFactory::creatForConcert($concert, ['created_at' => Carbon::parse('11 days ago')]);

        $response = $this->actingAs($user)->get("backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(200);
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_cannot_view_the_orders_of_unpublished_concert()
    {
        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->state('unpublished')->create(['user_id' => $user->id]);

        $response = $this->actingAs($user)->get("backstage/published-concerts/{$concert->id}/orders");

        $response->assertStatus(404);
    }
}
