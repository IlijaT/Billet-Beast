<?php

use Illuminate\Database\Seeder;
use App\Concert;
use App\User;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        $user = factory(User::class)->create(['name' => 'John Doe', 'email' => 'john@example.com']);
        $concertA = factory(Concert::class)->create(['user_id' => $user->id]);
        $concertA->publish();
        factory(Concert::class)->create(['title' => 'Another Example Band', 'user_id' => $user->id]);
    }
}
