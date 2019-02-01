<?php

namespace App\Repositories;

use App\Models\User\RedditToken;
use Prettus\Repository\Eloquent\BaseRepository;

class RedditTokenRepository extends BaseRepository
{
    public function model()
    {
        return RedditToken::class;
    }

    public function findByUserNameOrCreate($userData)
    {
        // FIXME: Huh? Isn't it suppose to be user_id?
        return RedditToken::updateOrCreate(
            ['username' => $userData->nickname],
            ['avatar' => strtok($userData->user['icon_img'], '?')]
        );
    }
}