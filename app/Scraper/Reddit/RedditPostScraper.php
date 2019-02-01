<?php

namespace App\Scraper\Reddit;

use App\Scraper\Base\BaseScraper;
use App\Scraper\Reddit\RequestManager\RedditPostsBulk;
use App\Scraper\Reddit\ResponseParser\RedditBulkPostsApiParser;
use App\Scraper\Reddit\Storage\PostsStorage;

class RedditPostScraper extends BaseScraper
{
    /**
     * RedditPostScraper constructor.
     * @param RedditPostsBulk $requestor
     * @param PostsStorage $storage
     */
    public function __construct(RedditPostsBulk $requestor, PostsStorage $storage)
    {
        parent::__construct(
            $requestor,
            new RedditBulkPostsApiParser,
            $storage
        );
    }
}