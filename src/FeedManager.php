<?php

namespace GetStream\Doctrine;

use GetStream\Stream\Client;
use GetStream\Stream\Feed;

class FeedManager implements FeedManagerInterface
{
    /**
     * @var Client
     */
    private $client;

    /**
     * @var string
     */
    private $userFeed = 'user';

    /**
     * @var string
     */
    private $notificationFeed = 'notification';

    /**
     * @var string[]
     */
    private $newsFeeds = [];

    /**
     * @param Client $client
     */
    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $userFeed
     *
     * @return $this
     */
    public function setUserFeed($userFeed)
    {
        $this->userFeed = $userFeed;

        return $this;
    }

    /**
     * @param string $notificationFeed
     *
     * @return $this
     */
    public function setNotificationFeed($notificationFeed)
    {
        $this->notificationFeed = $notificationFeed;

        return $this;
    }

    /**
     * @param array $newsFeeds
     *
     * @return $this
     */
    public function setNewsFeeds(array $newsFeeds)
    {
        $this->newsFeeds = $newsFeeds;

        return $this;
    }

    /**
     * @return Client
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $feed
     * @param string $id
     *
     * @return Feed
     */
    public function getFeed($feed, $id)
    {
        return $this->client->feed($feed, $id);
    }

    /**
     * @param string $userId
     *
     * @return Feed
     */
    public function getUserFeed($userId)
    {
        return $this->getFeed($this->userFeed, $userId);
    }

    /**
     * @param string $userId
     *
     * @return Feed
     */
    public function getNotificationFeed($userId)
    {
        return $this->getFeed($this->notificationFeed, $userId);
    }

    /**
     * @param string $userId
     *
     * @return Feed[]
     */
    public function getNewsFeeds($userId)
    {
        return array_map(function ($feed) use ($userId) {
            return $this->getFeed($feed, $userId);
        }, array_combine($this->newsFeeds, $this->newsFeeds));
    }

    /**
     * @param string $userId
     * @param string $targetUserId
     */
    public function followUser($userId, $targetUserId)
    {
        $newsFeeds = $this->getNewsFeeds($userId);
        $targetFeed = $this->getUserFeed($targetUserId);

        foreach ($newsFeeds as $feed) {
            $feed->followFeed($targetFeed->getSlug(), $targetFeed->getUserId());
        }
    }

    /**
     * @param string $userId
     * @param string $targetUserId
     */
    public function unfollowUser($userId, $targetUserId)
    {
        $newsFeeds = $this->getNewsFeeds($userId);
        $targetFeed = $this->getUserFeed($targetUserId);

        foreach ($newsFeeds as $feed) {
            $feed->unfollowFeed($targetFeed->getSlug(), $targetFeed->getUserId());
        }
    }
}
