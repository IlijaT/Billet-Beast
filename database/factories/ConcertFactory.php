<?php

/* @var $factory \Illuminate\Database\Eloquent\Factory */

use App\Concert;
use Carbon\Carbon;
use Faker\Generator as Faker;

$factory->define(Concert::class, function (Faker $faker) {
    return [
        'title' => 'Example Band',
        'subtitle' => 'With Fake Openers',
        'date'  => Carbon::parse('+2 weeks'),
        'ticket_price'  => 2000,
        'venue' => 'The Example Theatre', 
        'venue_address'  => 'Example Street 123',
        'city'  => 'Fakeville',
        'state' => 'Srbija',
        'zip' => '21000',
        'additional_information' => 'Some sample additinal information',
    ];
});
