<?php

namespace App\Repositories\Eloquent;

use App\Models\User\RedditToken;
use App\Repositories\Contract\TokenRepository;
use Illuminate\Support\Facades\DB;

class EloquentTokenRepository implements TokenRepository
{
    public function updateOrCreate($searchAgainst, $updateWith)
    {
        return RedditToken::updateOrCreate($searchAgainst, $updateWith);
    }

    /**
     * TODO: Global settings, fetch specified user token (hardcoding in database)
     * TODO: Change query to be based on `token_expires_in_seconds` column
     *
     * @return string|null
     */
    public function getFresh()
    {
        $token = RedditToken::select('access_token')
            ->where('expires_at', '>', DB::raw('DATE_ADD(NOW(), INTERVAL 5 MINUTE)'))
            ->limit(1)
            ->get()
            ->first();

        return $token !== null ? $token->access_token : null;
    }
}