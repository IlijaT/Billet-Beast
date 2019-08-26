<?php

namespace Tests\Unit\Listeners;

use App\Concert;
use Tests\TestCase;
use App\Events\ConcertAdded;
use App\Jobs\ProcessPosterImage;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Queue;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SchedulePosterImageProcessingTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_queues_a_job_to_process_a_poster_image_if_an_image_is_present()
    {
        Queue::fake();
        $concert = factory(Concert::class)->state('unpublished')->create([
            'poster_image_path'    => 'posters/example-poster.png'
        ]);

        ConcertAdded::dispatch($concert);

        Queue::assertPushed(ProcessPosterImage::class, function ($job) use ($concert) {
            return $job->concert->is($concert);
        });
    }

    /** @test */
    public function a_job_is_not_queued_if_a_poster_is_not_present()
    {
        Queue::fake();

        $concert = factory(Concert::class)->state('unpublished')->create([
            'poster_image_path'    => null
        ]);

        ConcertAdded::dispatch($concert);

        Queue::assertNotPushed(ProcessPosterImage::class);
    }
}
