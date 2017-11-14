<?php
namespace App\Service;

use App\Model\PullRequest;
use Smirik\PHPDateTimeAgo\DateTimeAgo;
use Smirik\PHPDateTimeAgo\TextTranslator\EnglishTextTranslator;

class CreateMessageService
{
    /**
     * @param \App\Model\PullRequest[] $pullRequests
     * @return string
     */
    public function create(array $pullRequests): string
    {
        $message = '';
        if (count($pullRequests) === 0) {
            return 'Hi! There are no open pull requests!! You are the best team there is!';
        }
        if (count($pullRequests) > 15) {
            $message .= "Hi! There are a lot of open pull requests you should take a look at: \n\n";
        } else {
            $message .= "Hi! There's a few open pull requests you should take a look at: \n\n";
        }

        foreach ($pullRequests as $pr) {
            $message .= $this->format($pr);
        }

        $message .= "\n#:speech_balloon: = # comments, :white_check_mark: = approved, :x: = changes requested, :warning: = not mergeable";

        return $message;
    }

    /**
     * @param \App\Model\PullRequest $pr
     * @return string
     */
    protected function format(PullRequest $pr): string
    {
        $textTranslator = new EnglishTextTranslator();
        $textTranslator->enableWeeksMonthsYears(true);
        $dateAgo = new DateTimeAgo($textTranslator);
        $daysAgo = $dateAgo->get($pr->createdAt);
        $mergeable = $pr->isMergeable ? '' : ':warning:';
        $reviews = $this->convertReviewsToIcons($pr->reviews, $pr->commentCount);
        return sprintf("*[%s/%s]* <%s|%s - by %s> _%s_ %s%s (%s) \n",
            $pr->organization,
            $pr->repository,
            $pr->url,
            $pr->title,
            $pr->author,
            $daysAgo,
            $reviews,
            $mergeable,
            $this->convertLanguageToIcon($pr->language)
        );
    }

    protected function convertLanguageToIcon($lang)
    {
        switch ($lang) {
            case 'PHP':
                return ':php:';
                break;
            case 'Swift':
                return ':swift:';
                break;
            case 'Kotlin':
                return ':kotlin:';
                break;
            case 'Java':
                return ':android:';
        }
        return '(' . $lang . ')';
    }

    protected function convertCommentCountToIcon(int $comments): string
    {
        if ($comments === 0) {
            return '';
        }
        return $comments.':speech_balloon: ';
    }

    protected function convertReviewsToIcons(array $reviews, int $comments): string
    {
        if (count($reviews) === 0) {
            return '';
        }

        $approved = 0;
        $commented = $comments;
        $changesRequested = 0;
        $icons = '';

        foreach ($reviews as $review) {
            switch ($review->state) {
                case 'APPROVED':
                    $approved++;
                    break;
                case 'COMMENTED':
                    $commented++;
                    break;
                case 'CHANGES_REQUESTED':
                    $changesRequested++;
                    break;
            }
        }
        $icons .= $this->reviewIcon($commented, ':speech_balloon:');
        $icons .= $this->reviewIcon($approved, ':white_check_mark:');
        $icons .= $this->reviewIcon($changesRequested, ':x:');
        return $icons;
    }

    protected function reviewIcon($total, $icon): string
    {
        if ($total === 1) {
            return $icon;
        } elseif ($total > 1) {
            return ' ' . $total . $icon;
        } else {
            return '';
        }
    }

}