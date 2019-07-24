<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Ticket;
use Faker\Generator as Faker;

$factory->define(Ticket::class, function (Faker $faker) {
    return [
        'concert_id' => function () {
            return factory(App\Concert::class)->create()->id;
        }
    ];
});
