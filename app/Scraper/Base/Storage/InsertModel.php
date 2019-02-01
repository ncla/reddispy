<?php

namespace App\Scraper\Base\Storage;

class InsertModel extends EloquentDatabaseStorage
{
    public function store($data)
    {
        $query = $this->model->newModelQuery();
        return $query->insertIgnore($data);
    }
}