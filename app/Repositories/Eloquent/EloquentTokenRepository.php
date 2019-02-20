<?php

namespace App\Repositories\Eloquent;

use App\Models\User\RedditToken;
use App\Repositories\Contract\TokenRepository;

class EloquentTokenRepository implements TokenRepository
{
    public function updateOrCreate($searchAgainst, $updateWith)
    {
        return RedditToken::updateOrCreate($searchAgainst, $updateWith);
    }

    /**
     * TODO: Global settings, fetch specified user token (hardcoding in database)
     *
     * @return string|null
     */
    public function getFresh()
    {
        $timeRequirement = now()->addMinutes(5)->toDateTimeString();

        $token = RedditToken::select('access_token')
            ->where('expires_at', '>', $timeRequirement)
            ->limit(1)
            ->get()
            ->first();

        return $token !== null ? $token->access_token : null;
    }
}
