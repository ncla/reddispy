<?php

namespace App\Scraper\Base;

use App\Scraper\Base\RequestManager\BaseRequestManager as RequestManager;
use App\Scraper\Base\ResponseParser\BaseResponseParser as Parser;
use App\Scraper\Base\Storage\DatabaseStorageInterface as Storage;

class BaseScraper implements ScraperInterface
{
    public $requestManager;
    public $parser;
    public $storage;

    // Storage resolves automatically if not provided? We don't really need it to be specific to a thing (Posts, History, Comments)
    public function __construct(RequestManager $requestManager, Parser $parser, Storage $storage)
    {
        $this->requestManager = $requestManager;
        $this->parser = $parser;
        $this->storage = $storage;
    }

    public function scrape()
    {
        $responses = $this->fetch();
        $parsed = $this->parse($responses);
        $this->store($parsed);
    }

    public function fetch()
    {
        $this->requestManager->scrape();
        return $this->requestManager->getAllResponses();
    }

    public function parse($responses)
    {
        $this->parser->iterateResponses($responses);
        return $this->parser->getAllParsedObjects();
    }

    public function store($items)
    {
        return $this->storage->store($items);
    }

}