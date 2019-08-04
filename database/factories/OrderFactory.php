<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Order;
use Faker\Generator as Faker;

$factory->define(Order::class, function (Faker $faker) {
    return [
        'email' => 'john@example.com',
        'amount' => 5250,
        'confirmation_number' => 'ORDERCONFIRMATION123',
        'card_last_four' => '1234'
    ];
});
