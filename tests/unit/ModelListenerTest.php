<?php

namespace GetStream\Doctrine\Unit;

use Cake\Chronos\Chronos;
use GetStream\Doctrine\FeedManagerInterface;
use GetStream\Doctrine\ModelListener;
use GetStream\Doctrine\Stubs\Activity;
use GetStream\Doctrine\Stubs\ActivityWithExtraData;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;
use GetStream\Stream\Client;

class ModelListenerTest extends TestCase
{
    /** @test */
    public function instantiation()
    {
        // Arrange
        // Act
        $manager = $this->createMock(FeedManagerInterface::class);
        $listener = new ModelListener($manager);

        // Assert
        $this->assertInstanceOf(ModelListener::class, $listener);
    }

    /** @test */
    public function activityCreated()
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
    public function activityUpdated()
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
            'object' => 'GetStream\Doctrine\Stubs\ActivityWithExtraData:1',
            'foreign_id' => 'GetStream\Doctrine\Stubs\ActivityWithExtraData:1',
            'time' => '2017-01-01T00:00:00+0000',
            'to' =>  [],
            'tags' => ['tag1', 'tag2'],
            'pinned' => false
        ]);

        $act = new ActivityWithExtraData();
        $result = $listener->activityCreated($act);

        $client = $this->createMock(Client::class);

        $manager->method('getClient')->willReturn($client);
        $client->method('updateActivities')->with([
            [
                'actor' => '\User:2',
                'verb' => 'like',
                'object' => 'GetStream\Doctrine\Stubs\ActivityWithExtraData:1',
                'foreign_id' => 'GetStream\Doctrine\Stubs\ActivityWithExtraData:1',
                'time' => '2017-01-01T00:00:00+0000',
                'to' =>  [],
                'tags' => ['test3', 'test4'],
                'pinned' => true
            ]
        ]);

        $act->setExtraData([
            'tags' => ['test3', 'test4'],
            'pinned' => true
        ]);

        // Act
        $result = $listener->activityUpdated($act);

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
