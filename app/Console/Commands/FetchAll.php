<?php

namespace App\Console\Commands;

use App\Scraper\RedditPostScraper;
use Illuminate\Console\Command;

class FetchAll extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'fetch:all';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @throws \ErrorException
     * @return mixed
     */
    public function handle()
    {
        $scraper = new RedditPostScraper;
        $scraper->scrape();
    }
}
