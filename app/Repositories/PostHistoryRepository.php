<?php

namespace App\Repositories;

use App\Models\Reddit\PostHistory;
use Prettus\Repository\Eloquent\BaseRepository;

class PostHistoryRepository extends BaseRepository
{
    public function model()
    {
        return PostHistory::class;
    }
}