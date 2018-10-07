<?php
namespace App\Service;

use App\Service\Formatter\PullRequest as PullRequestFormatter;

class CreatePersonalMessageService
{
    private $userLookup;
    private $messages;

    public function __construct($userLookup)
    {
        $this->userLookup = $userLookup;
        $this->messages = [];
    }

    /**
     * @param \App\Model\PullRequest[] $pullRequests
     * @return array
     */
    public function create(array $pullRequests): array
    {
        if (\count($pullRequests) === 0) {
            return $this->messages;
        }
        $pullRequestFormatter = new PullRequestFormatter();

        foreach ($pullRequests as $pr) {
            if (\count($pr->reviewRequests) > 0) {
                $prMessage = $pullRequestFormatter->format($pr);
                foreach ($pr->reviewRequests as $reviewRequest) {
                    if (!array_key_exists($reviewRequest->userId, $this->userLookup)) {
                        continue;
                    }
                    $this->addMessage($this->userLookup[$reviewRequest->userId], $prMessage);
                }
            }
        }
        return $this->messages;
    }

    protected function addMessage(string $channel, string $prMessage)
    {
        if (!array_key_exists($channel, $this->messages)) {
            $this->messages[$channel]  = "Hi! You have been requested to review the following Pull Requests: \n\n";
        }
        $this->messages[$channel] .= $prMessage . "\n";
    }
}
