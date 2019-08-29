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
    public function viewing_a_used_invitation()
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
    public function registering_with_a_valid_invitation_code()
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

    /** @test */
    public function registering_with_a_used_invitation_code()
    {

        factory(Invitation::class)->create([
            'user_id' => factory(User::class)->create(),
            'code' => 'TESTCODE1234'
        ]);

        $this->assertEquals(1, User::count());

        $response = $this->post('/register', [
            'invitation_code' => 'TESTCODE1234',
            'email' => 'jane@example.com',
            'password' => 'secret'
        ]);

        $response->assertStatus(404);
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function registering_with_a_invitation_code_that_does_not_exist()
    {

        $response = $this->post('/register', [
            'invitation_code' => 'TESTCODE1234',
            'email' => 'jane@example.com',
            'password' => 'secret'
        ]);

        $response->assertStatus(404);
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_is_required_for_registering()
    {

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => '',
            'invitation_code' => 'TESTCODE1234',
            'password' => 'secret'
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_a_valid_email()
    {

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'not-an-email',
            'invitation_code' => 'TESTCODE1234',
            'password' => 'secret'
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(0, User::count());
    }

    /** @test */
    public function email_must_be_unique()
    {
        $existingUser = factory(User::class)->create(['email' => 'jane@example.com']);
        $this->assertEquals(1, User::count());

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'jane@example.com',
            'invitation_code' => 'TESTCODE1234',
            'password' => 'secret'
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('email');
        $this->assertEquals(1, User::count());
    }

    /** @test */
    public function password_is_required_for_registering()
    {

        $invitation = factory(Invitation::class)->create([
            'user_id' => null,
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->from('/invitations/TESTCODE1234')->post('/register', [
            'email' => 'jane@example.com',
            'invitation_code' => 'TESTCODE1234',
            'password' => ''
        ]);

        $response->assertRedirect('/invitations/TESTCODE1234');
        $response->assertSessionHasErrors('password');
        $this->assertEquals(0, User::count());
    }
}
