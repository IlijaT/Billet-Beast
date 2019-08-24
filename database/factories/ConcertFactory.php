<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\User;
use App\Concert;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => 'Example Band',
        'subtitle' => 'With Fake Openers',
        'additional_information' => 'Some sample additinal information',
        'date'  => Carbon::parse('+2 weeks'),
        'venue' => 'The Example Theatre',
        'venue_address'  => 'Example Street 123',
        'city'  => 'Fakeville',
        'state' => 'Srbija',
        'zip' => '21000',
        'user_id' => factory(User::class),
        'ticket_price'  => 2000,
        'ticket_quantity'  => 10,
    ];
});

$factory->state(Concert::class, 'published', function ($faker) {
    return ['published_at' => Carbon::now()];
});

$factory->state(Concert::class, 'unpublished', function ($faker) {
    return ['published_at' => null];
});
