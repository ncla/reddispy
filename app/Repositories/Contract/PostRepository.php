<?php

namespace App\Repositories\Contract;

interface PostRepository
{
    public function insertIgnore($entries);

    public function findPostIds($ids);
}