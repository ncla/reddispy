<?php

namespace App\Repositories\Contract;

interface UserRepository
{
    public function updateOrCreate($searchAgainst, $updateWith);
}
