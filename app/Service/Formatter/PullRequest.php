<?php
namespace App\Service\Formatter;

use Smirik\PHPDateTimeAgo\TextTranslator\EnglishTextTranslator;
use Smirik\PHPDateTimeAgo\DateTimeAgo;
use App\Model\PullRequest as PullRequestModel;

class PullRequest
{
    /**
     * @param PullRequestModel $pr
     * @return string
     */
    public function format(PullRequestModel $pr): string
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
            str_replace(['&', '<', '>'], ['&amp;', '&lt;', '&gt;'], $pr->title),
            $pr->author,
            $daysAgo,
            $reviews,
            $mergeable,
            $this->convertLanguageToIcon($pr->language)
        );
    }

    protected function convertLanguageToIcon($lang): string
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
        return $lang;
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
        if ($comments === 0 && \count($reviews) === 0) {
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
        }
        if ($total > 1) {
            return ' ' . $total . $icon;
        }
        return '';
    }
}
