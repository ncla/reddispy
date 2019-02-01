<?php

namespace App\Repositories;

use App\Models\Reddit\Post;
use Prettus\Repository\Eloquent\BaseRepository;

class PostRepository extends BaseRepository
{
    public function model()
    {
        return Post::class;
    }
}