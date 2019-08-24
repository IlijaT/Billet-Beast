<?php

namespace Tests\Feature\Backstage;

use App\User;
use App\Concert;
use Tests\TestCase;
use App\AttendeeMessage;
use App\Jobs\SendAttendeeMessage;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MessageAttendeesTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function a_promoter_can_view_message_form_from_their_own_concert()
    {
        $this->withoutExceptionHandling();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(200);
        $response->assertViewIs('backstage.concert-messages.create');
        $this->assertTrue($response->data('concert')->is($concert));
    }

    /** @test */
    public function a_promoter_cannot_view_message_form_from_another_concert()
    {

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create([
            'user_id' => factory(User::class)->create()
        ]);
        $concert->publish();

        $response = $this->actingAs($user)->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertStatus(404);
    }

    /** @test */
    public function a_guest_cannot_view_message_form_from_any_concert()
    {

        $concert = factory(Concert::class)->create();
        $concert->publish();

        $response = $this->get("/backstage/concerts/{$concert->id}/messages/new");

        $response->assertRedirect('/login');
    }

    /** @test */
    public function a_promoter_can_send_new_message()
    {
        $this->withoutExceptionHandling();

        Queue::fake();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My subject',
            'message' => 'My message'
        ]);

        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $response->assertSessionHas('flash');

        $message = AttendeeMessage::first();
        $this->assertEquals($concert->id, $message->concert_id);
        $this->assertEquals('My subject', $message->subject);
        $this->assertEquals('My message', $message->message);

        Queue::assertPushed(SendAttendeeMessage::class, function ($job) use ($message) {
            return $job->attendeeMessage->is($message);
        });
    }

    /** @test */
    public function a_promoter_cannot_send_messages_for_another_promoters_concerts()
    {
        Queue::fake();

        $user = factory(User::class)->create();
        $otherUserConcert = factory(Concert::class)->create([
            'user_id' => factory(User::class)->create()
        ]);
        $otherUserConcert->publish();

        $response = $this->actingAs($user)->post("/backstage/concerts/{$otherUserConcert->id}/messages", [
            'subject' => 'My Subject',
            'message' => 'My Message'
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_guest_cannot_send_messages_for_any_concerts()
    {
        Queue::fake();

        $concert = factory(Concert::class)->create([
            'user_id' => factory(User::class)->create()
        ]);
        $concert->publish();

        $response = $this->post("/backstage/concerts/{$concert->id}/messages", [
            'subject' => 'My Subject',
            'message' => 'My Message'
        ]);

        $response->assertRedirect('/login');
        $this->assertEquals(0, AttendeeMessage::count());
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_subject_is_required()
    {
        Queue::fake();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/messages/new")
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'message' => 'My Message'
            ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $this->assertEquals(0, AttendeeMessage::count());
        $response->assertSessionHasErrors('subject');
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }

    /** @test */
    public function a_message_body_is_required()
    {

        Queue::fake();

        $user = factory(User::class)->create();
        $concert = factory(Concert::class)->create(['user_id' => $user->id]);
        $concert->publish();

        $response = $this->actingAs($user)
            ->from("/backstage/concerts/{$concert->id}/messages/new")
            ->post("/backstage/concerts/{$concert->id}/messages", [
                'subject' => 'My Subject'
            ]);

        $response->assertStatus(302);
        $response->assertRedirect("/backstage/concerts/{$concert->id}/messages/new");
        $this->assertEquals(0, AttendeeMessage::count());
        $response->assertSessionHasErrors('message');
        Queue::assertNotPushed(SendAttendeeMessage::class);
    }
}
