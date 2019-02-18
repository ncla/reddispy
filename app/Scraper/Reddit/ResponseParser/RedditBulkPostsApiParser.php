<?php

namespace App\Scraper\Reddit\ResponseParser;

use App\Scraper\Base\ResponseParser\BaseResponseParser;
use Psr\Http\Message\ResponseInterface;

class RedditBulkPostsApiParser extends BaseResponseParser
{
    public function iterateResponses($responses)
    {
        /* @var $responses ResponseInterface[] */
        foreach ($responses as $response) {
            $jsonParsed = json_decode($response->getBody());

            foreach ($jsonParsed->data->children as $postApiData) {
                $transformed = $this->transformNodeIntoObject($postApiData);
                $this->addObjectToParsedItems($transformed);
            }
        }
    }

    protected function transformNodeIntoObject($data)
    {
        $data = $data->data;

        return [
            'reddit_id' => $data->id,
            'title' => $data->title,
            'post_text' => $data->selftext === '' ? null : $data->selftext,
            'score' => $data->score,
            'view_count' => $data->view_count,
            'upvote_ratio' => null,
            'subreddit_id' => str_replace('t5_', '', $data->subreddit_id),
            'subreddit_name' => $data->subreddit,
            'submitted_at' => date('Y-m-d H:i:s', $data->created)
        ];
    }
}
