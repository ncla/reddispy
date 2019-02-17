<?php

namespace App\Providers;

use App\Repositories\Contract\PostHistoryRepository;
use App\Repositories\Contract\PostRepository;
use App\Repositories\Contract\TokenRepository;
use App\Repositories\Contract\UserRepository;
use App\Repositories\Eloquent\EloquentPostHistoryRepository;
use App\Repositories\Eloquent\EloquentPostRepository;
use App\Repositories\Eloquent\EloquentTokenRepository;
use App\Repositories\Eloquent\EloquentUserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryProvider extends ServiceProvider
{
    public $bindings = [
        UserRepository::class => EloquentUserRepository::class,
        TokenRepository::class => EloquentTokenRepository::class,
        PostRepository::class => EloquentPostRepository::class,
        PostHistoryRepository::class => EloquentPostHistoryRepository::class
    ];

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
