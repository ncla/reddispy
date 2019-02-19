<?php

namespace App\Repositories\Contract;

interface PostRepository
{
    public function insert($entries);

    public function insertIgnore($entries);

    public function findPostIds($ids);
}
