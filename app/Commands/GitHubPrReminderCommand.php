<?php

namespace App\Commands;

use App\Service\GitHubPrFinderService;
use App\Service\SlackService;
use Illuminate\Console\Scheduling\Schedule;
use LaravelZero\Framework\Commands\Command;
use App\Service\CreateMessageService;
use App\Service\CreatePersonalMessageService;

class GitHubPrReminderCommand extends Command
{
    /**
     * @var \App\Service\GitHubPrFinderService;
     */
    protected $gitHubPrFinderService;

    /**
     * @var \App\Service\SlackService;
     */
    protected $slackService;

    /**
     * The name and signature of the command.
     *
     * @var string
     */
    protected $signature = 'github-pr-reminder {--D|dry-run : Only send output to the console}';

    /**
     * The description of the command.
     *
     * @var string
     */
    protected $description = 'Displays all open GitHub PR\'s in a Slack message';

    public function __construct(GitHubPrFinderService $gitHubPrFinderService, SlackService $slackService)
    {
        $this->gitHubPrFinderService = $gitHubPrFinderService;
        $this->slackService = $slackService;
        parent::__construct();
    }


    /**
     * Execute the command. Here goes the code.
     *
     * @return void
     */
    public function handle(): void
    {
        $messageCreator = new CreateMessageService();
        $pullRequests = [];
        $organizations = config('git-hub.organizations');
        if (count($organizations) === 0) {
            $this->error('Please provide a least one organization in the config.');
            exit(1);
        }

        foreach ($organizations as $organization) {
            $pullRequests = array_merge($pullRequests, $this->gitHubPrFinderService->findPullRequests($organization, config('git-hub.exclude-labels')));
        }
        $message = $messageCreator->create($pullRequests);

        if($this->option('dry-run')) {
            $this->info('Slack message for ' . config('slack.channel') . ':');
            $this->line($message);
        } else {
            $this->slackService->postMessage($message, config('slack.channel'));
        }

        // Create personal messages for pull request reviews
        $personalMessageCreator = new CreatePersonalMessageService(config('git-hub.user-lookup'));
        $messages = $personalMessageCreator->create($pullRequests);

        foreach ($messages as $channel => $message) {
            if($this->option('dry-run')) {
                $this->info('Personal Slack message for user ' . $channel . ':');
                $this->line($message);
            } else {
                $this->slackService->postMessage($message, $channel);
            }
        }
    }

    /**
     * Define the command's schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule $schedule
     *
     * @return void
     */
    public function schedule(Schedule $schedule): void
    {
        // $schedule->command(static::class)->everyMinute();
    }
}
