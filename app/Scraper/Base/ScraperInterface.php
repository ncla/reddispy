<?php


namespace App\Scraper\Base;


interface ScraperInterface
{
    public function fetch();

    public function parse($responses);

    public function store($items);
}