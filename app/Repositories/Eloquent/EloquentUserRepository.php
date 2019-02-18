<?php

namespace App\Repositories\Eloquent;

use App\Models\User;
use App\Repositories\Contract\UserRepository;

class EloquentUserRepository implements UserRepository
{
    public function updateOrCreate($searchAgainst, $updateWith)
    {
        return User::updateOrCreate($searchAgainst, $updateWith);
    }
}
