<?php

use App\User;
use App\Concert;
use App\OrderFactory;
use Illuminate\Database\Seeder;

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

        OrderFactory::creatForConcert($concertA, ['email' => 'marijanamirkovic@example.com']);
        OrderFactory::creatForConcert($concertA, ['email' => 'vojislavmirkovic@example.com']);
    }
}
