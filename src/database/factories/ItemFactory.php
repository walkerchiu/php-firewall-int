<?php

/** @var \Illuminate\Database\Eloquent\Factory  $factory */

use Faker\Generator as Faker;
use WalkerChiu\Firewall\Models\Entities\Item;

$factory->define(Item::class, function (Faker $faker) {
    return [
        'setting_id' => 1,
        'user_id'    => 1
    ];
});
