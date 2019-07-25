<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Ticket;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
            return factory(App\Concert::class)->create()->id;
        }
    ];
});

$factory->state(Ticket::class, 'reserved', function ($faker) {
    return [ 'reserved_at' => Carbon::now() ];
});
