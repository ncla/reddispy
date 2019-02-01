<?php

namespace App\Providers;

use App\Scraper\Base\Storage\DatabaseStorageInterface;
use App\Scraper\Base\Storage\EloquentDatabaseStorage;
use App\Scraper\Base\Storage\InsertModel;
use App\Scraper\Base\RequestManager\BaseRequestManager;
use App\Scraper\Base\RequestManager\BaseOneByOneRequestManager;
use GuzzleHttp\Pool;
use GuzzleHttp\Promise\PromisorInterface;
use Illuminate\Support\ServiceProvider;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Client;

class AppServiceProvider extends ServiceProvider
{
    public $bindings = [
        ClientInterface::class => Client::class,
        BaseRequestManager::class => BaseOneByOneRequestManager::class,
        DatabaseStorageInterface::class => InsertModel::class,
        PromisorInterface::class => Pool::class
    ];

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {

    }
}
