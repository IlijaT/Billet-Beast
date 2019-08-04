<?php

namespace Tests\Unit;

use App\Order;
use App\Ticket;
use App\Concert;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use App\Exceptions\NotEnoughTicketsException;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ConcertTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date()
    {
        
        $concert = factory(Concert::class)->make([
            'date'  => Carbon::parse('2019-12-01, 8:00pm'),
        ]);
      
        $this->assertEquals('December 1, 2019', $concert->formatted_date);
    }

    /** @test */
    public function can_get_formatted_start_time()
    {
    
        $concert = factory(Concert::class)->make([
            'date'  => Carbon::parse('2019-12-01, 17:00:00'),
        ]);
        
        $this->assertEquals('5:00pm', $concert->formatted_start_time);
    }

    /** @test */
    public function can_get_ticket_price_in_dollars()
    {
        
        $concert = factory(Concert::class)->make([
            'ticket_price'  => 450,
        ]);
        
        $this->assertEquals('4.50', $concert->ticket_price_in_dollars);
    }

    /** @test */
    public function concerts_with_published_at_date_are_published()
    {
        $publishedConcertA = factory(Concert::class)->create([ 'published_at'   => Carbon::parse('-1 week') ]);
        $publishedConcertB = factory(Concert::class)->create([ 'published_at'   => Carbon::parse('-1 week') ]);
        $unpublishedConcertC = factory(Concert::class)->create([ 'published_at' => null ]);

        $publishedConcerts = Concert::published()->get();

        $this->assertTrue($publishedConcerts->contains($publishedConcertA));
        $this->assertTrue($publishedConcerts->contains($publishedConcertB));
        $this->assertFalse($publishedConcerts->contains($unpublishedConcertC));
        
    }

    /** @test */
    public function can_add_tickets()
    {
        $concert = factory(Concert::class)->create();
        $concert->addTickets(50);

        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function tickets_remaining_does_not_include_tickets_associated_with_an_order()
    {
        $concert = factory(Concert::class)->create();
        $concert->tickets()->saveMany(factory(Ticket::class, 30)->create(['order_id' => 1]));
        $concert->tickets()->saveMany(factory(Ticket::class, 20)->create());

        $this->assertEquals(20, $concert->ticketsRemaining());
    }

    /** @test */
    public function trying_to_reserve_more_tickets_than_remain_throws_an_exception()
    {
        $concert = factory(Concert::class)->create()->addTickets(10);

        try{
            $reservation = $concert->reserveTickets(11, 'jane@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertFalse($concert->hasOrderFor('jane@example.com'));
            $this->assertEquals(10, $concert->ticketsRemaining());
            return;
        }

        $this->fail('Order successed even there no enough tickets remaining');
    }

    /** @test */
    public function can_reserve_available_tickets()
    {
        $concert = factory(Concert::class)->create()->addTickets(3);
        $this->assertEquals(3, $concert->ticketsRemaining());

        $reservation = $concert->reserveTickets(2, 'john@example.com');

        $this->assertCount(2, $reservation->tickets());
        $this->assertEquals('john@example.com', $reservation->email());
        $this->assertEquals(1, $concert->ticketsRemaining());

    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_purchased()
    {

        $concert = factory(Concert::class)->create()->addTickets(3);
        $order = factory(Order::class)->create();
        $order->tickets()->saveMany($concert->tickets->take(2));
        //$concert->orderTickets('jane@example.com', 2);

        try {
            $concert->reserveTickets(2, 'john@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }
        
        $this->fail('Succeded even it shouldnt because tickets have already beeb sold');
    }

    /** @test */
    public function cannot_reserve_tickets_that_have_already_been_reserved()
    {

        $concert = factory(Concert::class)->create()->addTickets(3);
        $concert->reserveTickets(2, 'jane@example.com');

        try {
            $concert->reserveTickets(2, 'johne@example.com');
        } catch (NotEnoughTicketsException $e) {
            $this->assertEquals(1, $concert->ticketsRemaining());
            return;
        }
        
        $this->fail('Succeded even it shouldnt because tickets have already beeb reserved');
    }
}
