<?php

namespace GetStream\Doctrine\Unit;

use GetStream\Doctrine\ReactionSubscriber;
use Cake\Chronos\Chronos;
use GetStream\Doctrine\FeedManagerInterface;
use GetStream\Doctrine\ModelListener;
use GetStream\Doctrine\Stubs\Activity;
use GetStream\Stream\Client;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;

class ReactionSubscriberTest extends TestCase
{
    /** @test */
    public function instantiation()
    {
        // Arrange
        // Act
        $client = $this->createMock(Client::class);
        $subscriber = new ReactionSubscriber($client);

        // Assert
        $this->assertInstanceOf(ReactionSubscriber::class, $subscriber);
    }

    /** @test */
    public function testReactionPrePersist()
    {
        // Arrange
        $manager = $this->createMock(FeedManagerInterface::class);
        $feed = $this->createMock(Feed::class);
        Chronos::setTestNow(new Chronos('2017-01-01T00:00:00+00:00'));
        $listener = new ModelListener($manager);

        $manager->method('getUserFeed')->with('2')->willReturn($feed);
        $feed->method('addActivity')->with([
            'actor' => '\User:2',
            'verb' => 'like',
            'object' => 'GetStream\Doctrine\Stubs\Activity:1',
            'foreign_id' => 'GetStream\Doctrine\Stubs\Activity:1',
            'time' => '2017-01-01T00:00:00+0000',
            'to' =>  [],
        ]);

        // Act
        $result = $listener->activityCreated(new Activity());

        // Assert
        $this->assertNull($result);
    }

    /** @test */
    public function activityDeleting()
    {
        // Arrange
        $manager = $this->createMock(FeedManagerInterface::class);
        $feed = $this->createMock(Feed::class);
        $listener = new ModelListener($manager);

        $manager->method('getUserFeed')->with('2')->willReturn($feed);
        $feed->method('removeActivity')->with('GetStream\Doctrine\Stubs\Activity:1', true);

        // Act
        $result = $listener->activityDeleting(new Activity());

        // Assert
        $this->assertNull($result);
    }
}
