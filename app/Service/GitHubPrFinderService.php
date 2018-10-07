<?php
namespace App\Service;

use App\Mapper\PullRequestMapper;
use GitHub\Client as GitHubClient;

class GitHubPrFinderService
{
    /**
     * @var \GitHub\Client
     */
    protected $gitHubClient;

    protected $logger;

    public function __construct(GitHubClient $gitHubClient, $token)
    {
        $this->gitHubClient = $gitHubClient;
        $this->gitHubClient->authenticate($token, '', GitHubClient::AUTH_HTTP_TOKEN);
    }

    /**
     * @param array $organization
     * @param array $excludeLabels
     * @return \App\Model\PullRequest[]
     */
    public function findPullRequests(array $organization, array $excludeLabels)
    {
        $openPullRequests = [];
        $repositories = $this->findRepositories($organization['name']);
        $pullRequestMapper = new PullRequestMapper();
        foreach ($repositories as $repository) {
            if ($this->skipRepository($repository['name'], $organization)) {
                continue;
            }
            $prs = $this->gitHubClient->api("pull_request")->all($organization['name'], $repository['name']);
            foreach ($prs as $pr) {
                try {
                    $pr = $this->gitHubClient->api("pull_request")->show($organization['name'], $repository['name'], $pr['number']);
                    if ($this->skipPr($pr['title'], $excludeLabels)) {
                        continue;
                    }
                    $reviews = $this->gitHubClient->api('pull_request')->reviews()->all($organization['name'], $repository['name'], $pr['number']);
                    $reviewRequests = $this->gitHubClient->api('pull_request')->reviewRequests()->all($organization['name'], $repository['name'], $pr['number']);
                } catch (\Exception $e) {
                    $reviews = [];
                }
                $pullRequest = $pullRequestMapper->map($repository, $pr, $reviews, $reviewRequests);
                $openPullRequests[] = $pullRequest;
            }
        }
        return $openPullRequests;
    }

    /**
     * @param string $name
     * @param array $organization
     * @return bool
     */
    protected function skipRepository(string $name, array $organization): bool
    {
        if (isset($organization['include-repositories']) && count($organization['include-repositories']) > 0) {
            return !in_array($name, $organization['include-repositories']);
        } else if (isset($organization['exclude-repositories']) && count($organization['exclude-repositories']) > 0) {
            return in_array($name, $organization['exclude-repositories']);
        }

        return false;
    }

    /**
     * @param string $name
     * @param array $excludeLabels
     * @return bool
     */
    protected function skipPr(string $name, array $excludeLabels): bool
    {
        foreach($excludeLabels as $label) {
            if (stripos($name, $label) !== false) return true;
        }
        return false;
    }

    /**
     * @param string $organization
     * @return mixed
     */
    protected function findRepositories(string $organization)
    {
        $repositories = [];
        $page = 1;
        $perPage = 100;

        do {
            try {
                $repos = $this->gitHubClient->api('organization')->setPerPage($perPage)->repositories($organization, 'all', $page);
                $repositories = array_merge($repositories,$repos);
                $page++;
                $complete = (count($repos) !== $perPage);
            } catch (\Exception $e) {
                $complete = true;
            }

        } while ($complete === false);
        return $repositories;
    }
}
