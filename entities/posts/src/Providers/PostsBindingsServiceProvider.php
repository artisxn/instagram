<?php

namespace InetStudio\Instagram\Posts\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Contracts\Support\DeferrableProvider;

/**
 * Class PostsBindingsServiceProvider.
 */
class PostsBindingsServiceProvider extends ServiceProvider implements DeferrableProvider
{
    /**
    * @var  array
    */
    public $bindings = [
        'InetStudio\Instagram\Posts\Contracts\Repositories\PostsRepositoryContract' => 'InetStudio\Instagram\Posts\Repositories\PostsRepository',
        'InetStudio\Instagram\Posts\Contracts\Models\PostModelContract' => 'InetStudio\Instagram\Posts\Models\PostModel',
        'InetStudio\Instagram\Posts\Contracts\Services\Back\PostsServiceContract' => 'InetStudio\Instagram\Posts\Services\Back\PostsService',
    ];

    /**
     * Получить сервисы от провайдера.
     *
     * @return  array
     */
    public function provides()
    {
        return array_keys($this->bindings);
    }
}
