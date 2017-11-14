<?php

namespace App\Providers;

use App\Service\GitHubPrFinderService;
use App\Service\SlackService;
use Illuminate\Support\ServiceProvider;
use Github\Client as GithubClient;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(GitHubPrFinderService::class, function ($app) {
            return new GitHubPrFinderService(new GithubClient(), config('git-hub.token'));
        });

        $this->app->singleton(SlackService::class, function ($app) {
            return new SlackService(config('slack.token'), config('slack.bot-name'), config('slack.bot-icon'));
        });
    }
}
