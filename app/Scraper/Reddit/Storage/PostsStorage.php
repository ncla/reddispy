<?php

namespace App\Scraper\Reddit\Storage;

use App\Repositories\PostHistoryRepository;
use App\Repositories\PostRepository;
use App\Scraper\Base\Storage\DatabaseStorageInterface;

class PostsStorage implements DatabaseStorageInterface
{
    protected $postsRepository;
    protected $postsHistoryRepository;

    public function __construct(
        PostRepository $postsRepository,
        PostHistoryRepository $postHistoryRepository
    )
    {
        $this->postsRepository = $postsRepository;
        $this->postsHistoryRepository = $postHistoryRepository;
    }

    public function store($data)
    {
        $collection = collect($data);

        $postInsertFiltered = $collection->map(function($item) {
            return array_intersect_key($item, array_flip([
                'reddit_id',
                'title',
                'post_text',
                'subreddit_id',
                'subreddit_name',
                'submitted_at'
            ]));
        });

        $this->postsRepository->makeModel()->newModelQuery()->insertIgnore($postInsertFiltered->all());

        $postHistoryInsertFiltered = $collection->map(function($item) {
            $newItem = array_intersect_key($item, array_flip([
                'reddit_id',
                'score',
                'upvote_ratio',
                'view_count'
            ]));

            $newItem['post_reddit_id'] = $newItem['reddit_id'];
            unset($newItem['reddit_id']);

            return $newItem;
        });

        $this->postsHistoryRepository->makeModel()->newModelQuery()->insertIgnore($postHistoryInsertFiltered->all());
    }
}