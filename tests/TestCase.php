<?php

namespace Tests;

use PHPUnit\Framework\Assert;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Foundation\Testing\TestResponse;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;

    protected function setUp(): void
    {
        parent::setUp();

        TestResponse::macro('data', function ($keys) {
            return $this->original->getData()[$keys];
        });

        Collection::macro('assertContains', function ($value) {
            return Assert::assertTrue($this->contains($value), 'Failed asserting that collection contained specified value');
        });

        Collection::macro('assertNotContains', function ($value) {
            return Assert::assertFalse($this->contains($value), 'Failed asserting that collection did not contain specified value');
        });
    }

    public function from($url)
    {
        session()->setPreviousUrl(url($url));

        return $this;
    }
}
