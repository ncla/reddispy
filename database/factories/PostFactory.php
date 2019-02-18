<?php

use Faker\Generator as Faker;

$factory->define(App\Models\Reddit\Post::class, function (Faker $faker) {
    return [
        'reddit_id' => bin2hex(random_bytes(3)),
        'title' => $faker->text(150),
        'post_text' => $faker->paragraph(),
        'subreddit_id' => bin2hex(random_bytes(3)),
        'subreddit_name' => $faker->userName(),
        'submitted_at' => $faker->dateTime()
    ];
});
