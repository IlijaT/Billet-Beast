<?php

namespace Tests\Unit\Mail;

use Tests\TestCase;
use App\Invitation;
use App\Mail\InvitationEmail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class InvitationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_accept_the_invitation()
    {
        $invitation = factory(Invitation::class)->make([
            'email' => 'jane@example.com',
            'code' => 'TESTCODE1234'
        ]);

        $mail = new InvitationEmail($invitation);

        $this->assertContains(url('invitations/TESTCODE1234'), $mail->render());
    }
    /** @test */
    public function email_has_the_correct_subject()
    {
        $invitation = factory(Invitation::class)->make();

        $mail = new InvitationEmail($invitation);

        $this->assertEquals('You are invited to join BilletBeast', $mail->build()->subject);
    }
}
