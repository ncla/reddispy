<?php

namespace App\Scraper\Reddit\Storage;

use App\Repositories\Contract\PostHistoryRepository;
use App\Repositories\Contract\PostRepository;
use App\Scraper\Base\Storage\DatabaseStorageInterface;

class PostsStorage implements DatabaseStorageInterface
{
    protected $postsRepository;
    protected $postsHistoryRepository;

    public function __construct(
        PostRepository $postsRepository,
        PostHistoryRepository $postHistoryRepository
    ) {
        $this->postsRepository = $postsRepository;
        $this->postsHistoryRepository = $postHistoryRepository;
    }

    public function store($data)
    {
        $collection = collect($data);

        $postInsertFiltered = $collection->map(function ($item) {
            return array_intersect_key($item, array_flip([
                'reddit_id',
                'title',
                'post_text',
                'subreddit_id',
                'subreddit_name',
                'submitted_at'
            ]));
        });

        $idsPlucked = $postInsertFiltered->pluck('reddit_id');

        $postIdsExistInDatabase = $this->postsRepository->findPostIds($idsPlucked);

        // Filter posts that already exist in database to avoid incrementing AUTO_INCREMENT with INSERT IGNORE queries
        $postsInsert = $postInsertFiltered->whereNotIn('reddit_id', $postIdsExistInDatabase->pluck('reddit_id'));

        $this->postsRepository->insert($postsInsert->all());

        $postHistoryInsertFiltered = $collection->map(function ($item) {
            $newItem = array_intersect_key($item, array_flip([
                'reddit_id',
                'score',
                'upvote_ratio',
                'view_count'
            ]));

            $newItem['post_reddit_id'] = $newItem['reddit_id'];
            unset($newItem['reddit_id']);

            // SQLite in tests fails because it does not populate timestamp when doing bulk insert,
            // while doing the same on MySQL works just fine.
            $newItem['created_at'] = now()->toDateTimeString();

            return $newItem;
        });

        $this->postsHistoryRepository->insert($postHistoryInsertFiltered->all());
    }
}
