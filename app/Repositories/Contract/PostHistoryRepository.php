<?php

namespace App\Repositories\Contract;

interface PostHistoryRepository
{
    public function insert($entries);

    public function insertIgnore($entries);
}
