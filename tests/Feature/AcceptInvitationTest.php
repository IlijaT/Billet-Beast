<?php

namespace Tests\Feature;

use App\Invitation;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class AcceptInvitationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function viewing_an_unused_invitation()
    {
        $this->withoutExceptionHandling();

        $invitation = factory(Invitation::class)->create([
            'code' => 'TESTCODE1234'
        ]);

        $response = $this->get('/invitations/TESTCODE1234');

        $response->assertStatus(200);
        $response->assertViewIs('invitations.show');
        $this->assertTrue($response->data('invitation')->is($invitation));
    }
}
