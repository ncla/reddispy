<?php

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     * @throws Exception
     */
    public function run()
    {
        $subId = bin2hex(random_bytes(3));

        factory(App\Models\Reddit\Post::class, 50)->create([
            'subreddit_name' => 'Muse',
            'subreddit_id' => $subId
        ])->each(function ($post) {
            $post->history()->saveMany(factory(App\Models\Reddit\PostHistory::class, 10)->make());
        });
    }
}
