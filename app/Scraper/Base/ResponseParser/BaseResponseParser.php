<?php

namespace App\Scraper\Base\ResponseParser;

abstract class BaseResponseParser implements ResponseParserInterface
{
    protected $parsedItems = [];

    abstract public function iterateResponses($responses);

    abstract protected function transformNodeIntoObject($data);

    protected function addObjectToParsedItems($object)
    {
        $this->parsedItems[] = $object;
    }

    public function getAllParsedObjects()
    {
        return $this->parsedItems;
    }
}
