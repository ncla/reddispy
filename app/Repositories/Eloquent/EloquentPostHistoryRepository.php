<?php

namespace App\Repositories\Eloquent;

use App\Models\Reddit\PostHistory;
use App\Repositories\Contract\PostHistoryRepository;

class EloquentPostHistoryRepository implements PostHistoryRepository
{
    public function insert($entries)
    {
        return PostHistory::insert($entries);
    }

    public function insertIgnore($entries)
    {
        return PostHistory::insertIgnore($entries);
    }
}
