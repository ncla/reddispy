<?php

namespace App\Scraper\Base\Storage;


interface DatabaseStorageInterface
{
    public function store($data);
}