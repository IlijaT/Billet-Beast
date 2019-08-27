<?php

namespace Tests\Unit\Jobs;

use App\Concert;
use App\Jobs\ProcessPosterImage;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;

class ProcessPosterImageTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_resizes_the_poster_image_to_600px_wide()
    {
        Storage::fake('public');

        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/_fixtures_/full-size-poster.png'))
        );

        $concert = factory(Concert::class)->state('unpublished')->create([
            'poster_image_path'    => 'posters/example-poster.png'
        ]);

        ProcessPosterImage::dispatch($concert);

        $resizedImage = Storage::disk('public')->get('posters/example-poster.png');
        list($width, $height) = getimagesizefromstring($resizedImage);
        $this->assertEquals(600, $width);
        $this->assertEquals(776, $height);
    }

    /** @test */
    public function it_optimizes_the_poster_image()
    {
        Storage::fake('public');

        Storage::disk('public')->put(
            'posters/example-poster.png',
            file_get_contents(base_path('tests/_fixtures_/small-unoptimized-poster.png'))
        );

        $concert = factory(Concert::class)->state('unpublished')->create([
            'poster_image_path'    => 'posters/example-poster.png'
        ]);

        ProcessPosterImage::dispatch($concert);

        $optimizedImageSize = Storage::disk('public')->size('posters/example-poster.png');
        $originalImageSize = filesize(base_path('tests/_fixtures_/small-unoptimized-poster.png'));
        $this->assertLessThan($originalImageSize, $optimizedImageSize);
    }
}
