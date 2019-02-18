<?php

namespace App\Repositories\Contract;

interface TokenRepository
{
    public function updateOrCreate($searchAgainst, $updateWith);

    public function getFresh();
}
