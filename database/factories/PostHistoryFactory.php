<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Reddit\PostHistory::class, function (Faker $faker) {
    return [
        'post_reddit_id' => bin2hex(random_bytes(3)),
        'score' => $faker->numberBetween(0, 1500),
        'upvote_ratio' => $faker->randomFloat(5, 0, 1),
        'view_count' => $faker->numberBetween(1, 1500)
    ];
});
