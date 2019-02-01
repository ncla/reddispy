<?php

namespace App\Scraper\Base\ResponseParser;

interface ResponseParserInterface
{
    public function iterateResponses($responses);
}