<?php

namespace App\Repositories\Contract;

interface PostHistoryRepository
{
    public function insertIgnore($entries);
}