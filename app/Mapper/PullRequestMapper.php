<?php
namespace App\Mapper;

use App\Model\ReviewRequest;
use App\Model\PullRequest;
use App\Model\Review;
use DateTime;

class PullRequestMapper
{
    /**
     * @param array $repositoryData
     * @param array $pullRequestData
     * @param array $reviews
     * @param array $reviewRequests
     *
     * @return \App\Model\PullRequest ;
     * @internal param array $data
     */
    public function map(array $repositoryData, array $pullRequestData, array $reviews, array $reviewRequests): PullRequest
    {
        $pullRequest = new PullRequest();
        $pullRequest->title = $pullRequestData['title'];
        $pullRequest->organization = $repositoryData['owner']['login'];
        $pullRequest->repository = $pullRequestData['head']['repo']['name'];
        $pullRequest->author = $pullRequestData['user']['login'];
        $pullRequest->commentCount = $pullRequestData['comments'];
        $pullRequest->url = $pullRequestData['html_url'];
        $pullRequest->createdAt = new DateTime($pullRequestData['created_at']);
        $pullRequest->reviewCount = count($reviews);
        $pullRequest->reviews = $this->mapReviews($reviews);
        $pullRequest->isMergeable = $pullRequestData['mergeable'] === false? false : true;
        $pullRequest->language = $pullRequestData['base']['repo']['language'];
        $pullRequest->reviewRequests = $this->mapReviewRequests($reviewRequests);

        return $pullRequest;
    }

    protected function mapReviewRequests(array $reviewRequests): array
    {
        $mappedReviewRequests = [];
        // For now we only support users
        if (\count($reviewRequests['users']) === 0) {
            return $mappedReviewRequests;
        }
        foreach ($reviewRequests['users'] as $reviewRequestData) {
            $reviewRequest = new ReviewRequest();
            $reviewRequest->userId = $reviewRequestData['login'];
            $reviewRequest->userType = 'github';
            $mappedReviewRequests[] = $reviewRequest;
        }

        return $mappedReviewRequests;
    }

    protected function mapReviews(array $reviews): array
    {
        $mappedReviews = [];
        if (\count($reviews) === 0) {
            return $mappedReviews;
        }
        foreach ($reviews as $reviewData) {
            $review = new Review();
            $review->author = $reviewData['user']['login'];
            $review->state = $reviewData['state'];
            $review->createdAt = new DateTime($reviewData['submitted_at']);
            $mappedReviews[] = $review;
        }

        return $mappedReviews;
    }
}
