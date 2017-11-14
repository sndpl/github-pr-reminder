<?php
namespace App\Model;

class PullRequest
{
    public $title;
    public $author;
    public $createdAt;
    public $organization;
    public $repository;
    public $url;
    public $commentCount;
    public $isMergeable;
    public $reviewCount;
    public $language;
    /**
     * @var \App\Model\Review[]
     */
    public $reviews = [];
}