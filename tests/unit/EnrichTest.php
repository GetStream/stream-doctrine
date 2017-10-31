<?php

namespace GetStream\Doctrine\Unit;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use GetStream\Doctrine\Enrich;
use GetStream\Doctrine\EnrichedActivity;
use GetStream\Doctrine\EnrichInterface;
use GetStream\Doctrine\Stubs\User;
use PHPUnit\Framework\TestCase;

class EnrichTest extends TestCase
{
    /** @test */
    public function instantiation()
    {
        // Arrange

        // Act
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $instance = new Enrich($entityManager);

        // Assert
        $this->assertInstanceOf(EnrichInterface::class, $instance);
    }

    /** @test */
    public function enrichActivitiesReturnsEmpty()
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $enrich = new Enrich($entityManager);

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
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $enrich = new Enrich($entityManager);

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
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $enrich = new Enrich($entityManager);
        $activities = [
            [
                'verb' => 'like',
            ],
            [
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
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $enrich = new Enrich($entityManager);
        $aggregatedActivities = [
            [
                'activities' => [
                    [
                        'verb' => 'like',
                    ],
                    [
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

    /** @test */
    public function enrichActivitiesWillEnrichNothingIfEnrichingFieldsIsEmpty()
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $enrich = new Enrich($entityManager);
        $enrich->setEnrichingFields([]);

        $activities = [
            [
                'actor' => User::class.':1',
                'object' => 'App\Entities\SomeObject:1',
            ],
            [
                'actor' => User::class.':2',
                'object' => 'App\Entities\SomeObject:2',
            ],
        ];

        // Act
        $result = $enrich->enrichActivities($activities);

        // Assert
        $this->assertCount(2, $result);
        $this->assertContainsOnlyInstancesOf(EnrichedActivity::class, $result);
        $this->assertSame(User::class.':1', $result[0]['actor']);
        $this->assertSame('App\Entities\SomeObject:2', $result[1]['object']);
    }

    /** @test */
    public function enrichActivitiesWillEnrichOnlyActor()
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $enrich = new Enrich($entityManager);

        $activities = [
            [
                'actor' => User::class.':1',
                'object' => null,
            ],
        ];

        $entityManager->method('getRepository')->with(User::class)->willReturn($repository);
        $repository->method('matching')->willReturn(new ArrayCollection([new User(1)]));

        // Act
        $result = $enrich->enrichActivities($activities);

        // Assert
        $this->assertCount(1, $result);
        $this->assertInstanceOf(User::class, $result[0]['actor']);
        $this->assertNull($result[0]['object']);
    }

    /** @test */
    public function enrichActivitiesWillNoteWhenReferenceIsNotFound()
    {
        // Arrange
        $entityManager = $this->createMock(EntityManagerInterface::class);
        $repository = $this->createMock(EntityRepository::class);
        $enrich = new Enrich($entityManager);

        $activities = [
            [
                'actor' => User::class.':1',
            ],
        ];

        $entityManager->method('getRepository')->with(User::class)->willReturn($repository);
        $repository->method('matching')->willReturn(new ArrayCollection([]));

        // Act
        $result = $enrich->enrichActivities($activities);

        // Assert
        $this->assertCount(1, $result);
        $this->assertFalse($result[0]->enriched());
        $this->assertSame([User::class => '1'], $result[0]->getNotEnrichedData());
    }
}
