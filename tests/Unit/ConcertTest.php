<?php

namespace Tests\Unit;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Concert;
use Carbon\Carbon;

class ConcertTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function can_get_formatted_date()
    {
        
        $concert = factory(Concert::class)->create([
            'date'  => Carbon::parse('2019-12-01, 8:00pm'),
        ]);
      
        $this->assertEquals('December 1, 2019', $concert->formatted_date);
    }

      /** @test */
      public function can_get_formatted_start_time()
      {
        $this->withoutExceptionHandling();
        
        $concert = factory(Concert::class)->create([
            'date'  => Carbon::parse('2019-12-01, 17:00:00'),
        ]);
      
        $this->assertEquals('5:00pm', $concert->formatted_start_time);
      }
}
