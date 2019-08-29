<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Invitation;
use Faker\Generator as Faker;

$factory->define(Invitation::class, function (Faker $faker) {
    return [
        'email' => 'somebody@example.com',
        'code' => 'TESTCODE12334'
    ];
});
