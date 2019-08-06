<?php

namespace Tests\Unit\Mail;

use App\Order;
use Tests\TestCase;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderConfirmationEmailTest extends TestCase
{
    /** @test */
    public function email_contains_a_link_to_the_order_confirmation_page()
    {
        $order = factory(Order::class)->make([
            'confirmation_number' => 'ORDERCONFIRMATION1234'
        ]);

        $email = new OrderConfirmationEmail($order);

        $rendered = $email->render();

        $this->assertContains(url('/orders/ORDERCONFIRMATION1234'), $rendered);

    }

    /** @test */
    public function email_has_a_subject()
    {
        $order = factory(Order::class)->make();
        $email = new OrderConfirmationEmail($order);

        $this->assertEquals('Your BilletBeast Order', $email->build()->subject);
    }

}
