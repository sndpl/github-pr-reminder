<?php
namespace App\Service;

use App\Service\Formatter\PullRequest as PullRequestFormatter;

class CreateMessageService
{
    /**
     * @param \App\Model\PullRequest[] $pullRequests
     * @return string
     */
    public function create(array $pullRequests): string
    {
        $message = '';
        if (\count($pullRequests) === 0) {
            return 'Hi! There are no open pull requests!! You are the best team there is!';
        }
        if (\count($pullRequests) > 15) {
            $message .= "Hi! There are a lot of open pull requests you should take a look at: \n\n";
        } else {
            $message .= "Hi! There's a few open pull requests you should take a look at: \n\n";
        }
        $formatter = new PullRequestFormatter();
        foreach ($pullRequests as $pr) {
            $message .= $formatter->format($pr);
        }

        $message .= "\n#:speech_balloon: = # comments, :white_check_mark: = approved, :x: = changes requested, :warning: = not mergeable";

        return $message;
    }

}
