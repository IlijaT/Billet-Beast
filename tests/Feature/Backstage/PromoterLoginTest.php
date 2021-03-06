<?php

namespace Tests\Feature\Backstage;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\User;
use Illuminate\Support\Facades\Auth;

class PromoterLoginTest extends TestCase
{

    use RefreshDatabase;

    /** @test */
    public function logging_in_vith_valid_credentials()
    {
        $user = factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('super-secret-password')
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'super-secret-password'
        ]);

        $response->assertRedirect('/backstage/concerts');
        $this->assertTrue(Auth::check());
        $this->assertTrue(Auth::user()->is($user));
    }

    /** @test */
    public function logging_in_vith_invalid_credentials()
    {

        factory(User::class)->create([
            'email' => 'jane@example.com',
            'password' => bcrypt('super-secret-password')
        ]);

        $response = $this->post('/login', [
            'email' => 'jane@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('email');

        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_in_with_an_account_that_does_not_exist()
    {
        $this->withoutExceptionHandling();

        $response = $this->post('/login', [
            'email' => 'nobody@example.com',
            'password' => 'wrong-password'
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHas('email');
        $this->assertFalse(Auth::check());
    }

    /** @test */
    public function logging_out_the_current_user()
    {
        $this->withoutExceptionHandling();


        Auth::login(factory(User::class)->create());

        $response = $this->post('/logout');

        $response->assertRedirect('/login');
        $this->assertFalse(Auth::check());
    }
}
