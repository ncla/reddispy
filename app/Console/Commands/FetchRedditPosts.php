<?php

namespace App\Console\Commands;

use App\Scraper\Reddit\RedditPostScraper;
use App\Services\TokenService;
use GuzzleHttp\Client;
use Illuminate\Console\Command;

class FetchRedditPosts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:posts {subreddits*} {--without-token} {--pages=} {--itemsPerPage=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Fetch posts from specified sub-reddits';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @param RedditPostScraper $scraper
     * @param TokenService $tokenService
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle(RedditPostScraper $scraper, TokenService $tokenService)
    {
        $subreddits = $this->argument('subreddits');
        $pages = $this->option('pages');
        $itemsPerPage = $this->option('itemsPerPage');

        if ($this->option('without-token') === false) {
            $token = $tokenService->getTokenFromDatabase();

            $this->info('User token is null: ' . var_export(is_null($token), 1));

            if ($token === null) {
                $this->info('No user oAuth tokens available, using dev API keys to acquire token');

                $token = $tokenService->getOneTimeToken(env('REDDIT_KEY'), env('REDDIT_SECRET'));

                $this->info('One time token is null: ' . var_export(is_null($token), 1));
            }

            $scraper->requestManager->setOption('token', $token);
        }

        $scraper->requestManager->setOption('subreddits', $subreddits);

        if ($pages !== null) {
            $scraper->requestManager->setOption('pages_per_subreddit', intval($pages));
        }

        if ($itemsPerPage !== null) {
            $scraper->requestManager->setOption('max_items_per_request', intval($itemsPerPage));
        }

        $scraper->scrape();
    }
}
