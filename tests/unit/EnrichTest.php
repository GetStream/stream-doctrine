<?php

namespace GetStream\Doctrine\Unit;

use GetStream\Doctrine\Enrich;
use GetStream\Doctrine\EnrichedActivity;
use GetStream\Doctrine\EnrichInterface;
use PHPUnit\Framework\TestCase;

class EnrichTest extends TestCase
{
    /** @test */
    public function instantiation()
    {
        // Arrange

        // Act
        $instance = new Enrich();

        // Assert
        $this->assertInstanceOf(EnrichInterface::class, $instance);
    }

    /** @test */
    public function enrichActivitiesReturnsEmpty()
    {
        // Arrange
        $enrich = new Enrich();

        // Act
        $result = $enrich->enrichActivities([]);

        // Assert
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function enrichAggregateActivitiesReturnsEmpty()
    {
        // Arrange
        $enrich = new Enrich();

        // Act
        $result = $enrich->enrichAggregatedActivities([]);

        // Assert
        $this->assertInternalType('array', $result);
        $this->assertEmpty($result);
    }

    /** @test */
    public function enrichActivitiesReturnsEnrichedActivities()
    {
        // Arrange
        $enrich = new Enrich();
        $activities = [
            [
                'actor' => 'user:1',
                'verb' => 'like',
            ],
            [
                'actor' => 'user:2',
                'verb' => 'like',
            ],
        ];

        // Act
        $result = $enrich->enrichActivities($activities);

        // Assert
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(EnrichedActivity::class, $result);
    }

    /** @test */
    public function enrichAggregatedActivitiesReturnsActivitiesWithEmbeddedEnrichedActivities()
    {
        // Arrange
        $enrich = new Enrich();
        $aggregatedActivities = [
            [
                'activities' => [
                    [
                        'actor' => 'user:1',
                        'verb' => 'like',
                    ],
                    [
                        'actor' => 'user:2',
                        'verb' => 'like',
                    ],
                ],
            ],
        ];

        // Act
        $result = $enrich->enrichAggregatedActivities($aggregatedActivities);

        // Assert
        $this->assertCount(1, $result);
        $this->assertArrayHasKey('activities', $result[0]);
        $this->assertCount(2, $result[0]['activities']);
        $this->assertContainsOnlyInstancesOf(EnrichedActivity::class, $result[0]['activities']);
    }
}
