<?php

use Faker\Generator as Faker;

$factory->define(App\Models\User\RedditToken::class, function (Faker $faker) {
    return [
        'user_id' => function () {
            return factory(App\Models\User::class)->create()->id;
        },
        'refresh_token' => $faker->sha256(),
        'access_token' => $faker->sha256(),
        'token_expires_in_seconds' => 3600
    ];
});
