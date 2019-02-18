<?php

namespace App\Repositories\Eloquent;

use App\Models\Reddit\Post;
use App\Repositories\Contract\PostRepository;

class EloquentPostRepository implements PostRepository
{
    public function insertIgnore($entries)
    {
        return Post::insertIgnore($entries);
    }

    public function findPostIds($ids)
    {
        return Post::select('reddit_id')
            ->whereIn('reddit_id', $ids)
            ->get();
    }
}
