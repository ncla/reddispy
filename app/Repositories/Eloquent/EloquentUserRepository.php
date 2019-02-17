<?php

namespace App\Repositories\Eloquent;

use App\Repositories\Contract\UserRepository;
use App\Models\User;

class EloquentUserRepository implements UserRepository
{
    public function updateOrCreate($searchAgainst, $updateWith)
    {
        return User::updateOrCreate($searchAgainst, $updateWith);
    }
}