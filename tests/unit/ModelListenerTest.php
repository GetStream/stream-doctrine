<?php

namespace GetStream\Doctrine\Unit;

use Cake\Chronos\Chronos;
use GetStream\Doctrine\FeedManagerInterface;
use GetStream\Doctrine\ModelListener;
use GetStream\Doctrine\Stubs\Activity;
use GetStream\Stream\Feed;
use PHPUnit\Framework\TestCase;

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
            'actor' => 'user:2',
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
