<?php

namespace GetStream\Doctrine\Unit;

use GetStream\Doctrine\FeedManager;
use GetStream\Doctrine\FeedManagerInterface;
use GetStream\Stream\Client;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;

class FeedManagerTest extends TestCase
{
    /** @test */
    public function instantiation()
    {
        // Arrange
        $client = $this->createMock(Client::class);

        // Act
        $instance = new FeedManager($client);

        // Assert
        $this->assertInstanceOf(FeedManagerInterface::class, $instance);
    }

    /** @test */
    public function getClient()
    {
        // Arrange
        $client = $this->createMock(Client::class);

        // Act
        $manager = new FeedManager($client);

        // Assert
        $this->assertSame($client, $manager->getClient());
    }

    /** @test */
    public function getFeed()
    {
        // Arrange
        $client = new Client('foo', 'bar');
        $manager = new FeedManager($client);

        // Act
        $feed = $manager->getFeed('user', 1);

        // Assert
        $this->assertSame('user:1', $feed->getId());
    }

    /** @test */
    public function getUserFeed()
    {
        // Arrange
        $client = new Client('foo', 'bar');
        $manager = new FeedManager($client);

        // Act
        $feed1 = $manager->getUserFeed(1);
        $feed2 = $manager->setUserFeed('FacebookUser')->getUserFeed(2);

        // Assert
        $this->assertSame('user:1', $feed1->getId());
        $this->assertSame('FacebookUser:2', $feed2->getId());
    }

    /** @test */
    public function getNotificationFeed()
    {
        // Arrange
        $client = new Client('foo', 'bar');
        $manager = new FeedManager($client);

        // Act
        $feed1 = $manager->getNotificationFeed(1);
        $feed2 = $manager->setNotificationFeed('AdminNotification')->getNotificationFeed(2);

        // Assert
        $this->assertSame('notification:1', $feed1->getId());
        $this->assertSame('AdminNotification:2', $feed2->getId());
    }

    /** @test */
    public function getNewsFeeds()
    {
        // Arrange
        $client = new Client('foo', 'bar');
        $manager = new FeedManager($client);

        // Act
        $feeds1 = $manager->getNewsFeeds(1);
        $feeds2 = $manager->setNewsFeeds(['news_1', 'news_2'])->getNewsFeeds(2);

        // Assert
        $this->assertEmpty($feeds1);
        $this->assertCount(2, $feeds2);
        $this->assertContainsOnlyInstancesOf(Feed::class, $feeds2);
        $this->assertSame(['news_1', 'news_2'], array_keys($feeds2));
        $this->assertSame(['news_1:2', 'news_2:2'], [$feeds2['news_1']->getId(), $feeds2['news_2']->getId()]);
    }

    /** @test */
    public function followUser()
    {
        // Arrange
        $client = $this->createMock(Client::class);
        $userFeed = $this->createMock(Feed::class);
        $newsFeed = $this->createMock(Feed::class);
        $manager = new FeedManager($client);

        // Shouldn't fail:
        $manager->followUser('1', '2');

        $manager->setNewsFeeds(['news']);

        $userFeed->method('getSlug')->willReturn('user');
        $userFeed->method('getUserId')->willReturn('2');
        $newsFeed->method('follow')->with('user', '2')->willReturn(null);

        $client->method('feed')->withConsecutive(['news', '1'], ['user', '2'])->willReturnOnConsecutiveCalls($newsFeed, $userFeed);

        // Act
        $result = $manager->followUser('1', '2');

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function unfollowUser()
    {
        // Arrange
        $client = $this->createMock(Client::class);
        $userFeed = $this->createMock(Feed::class);
        $newsFeed = $this->createMock(Feed::class);
        $manager = new FeedManager($client);

        // Shouldn't fail:
        $manager->unfollowUser('1', '2');

        $manager->setNewsFeeds(['news']);

        $userFeed->method('getSlug')->willReturn('user');
        $userFeed->method('getUserId')->willReturn('2');
        $newsFeed->method('unfollow')->with('user', '2')->willReturn(null);

        $client->method('feed')->withConsecutive(['news', '1'], ['user', '2'])->willReturnOnConsecutiveCalls($newsFeed, $userFeed);

        // Act
        $result = $manager->unfollowUser('1', '2');

        // Assert
        $this->assertNull($result);
    }
}
