<?php

namespace Tests\Feature;

use App\Invitation;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function viewing_an_unused_invitation()
    {
        $this->withoutExceptionHandling();

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }

    /** @test */
    public function viewing_an_used_invitation()
    {

        factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    /** @test */
    public function viewing_an_invitation_that_does_not_exist()
    {

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(404);
    }

    /** @test */
    public function registering_with_a_walid_invitation_code()
    {
        $this->withoutExceptionHandling();

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->post('/register', [
            'invitation_code' => 'TESTCODE1234',
            'email' => 'jane@example.com',
            'password' => 'secret'
        ]);

        $response->assertRedirect('/backstage/concerts');

        $this->assertEquals(1, User::count());
        $user = User::first();
        $this->assertAuthenticatedAs($user);
        $this->assertEquals('jane@example.com', $user->email);
        $this->assertTrue(Hash::check('secret', $user->password));
        $this->assertTrue($invitation->fresh()->user->is($user));
    }
}
