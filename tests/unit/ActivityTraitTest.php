<?php

namespace GetStream\Doctrine\Unit;

use GetStream\Doctrine\Stubs\Activity;
use PHPUnit\Framework\TestCase;

class ActivityTraitTest extends TestCase
{
    /** @test */
    public function object()
    {
        // Arrange
        $object = new Activity();

        // Act
        $result = $object->activityObject();

        // Assert
        $this->assertSame('GetStream\Doctrine\Stubs\Activity:1', $result);
    }

    /** @test */
    public function foreignId()
    {
        // Arrange
        $object = new Activity();

        // Act
        $result = $object->activityForeignId();

        // Assert
        $this->assertSame('GetStream\Doctrine\Stubs\Activity:1', $result);
    }

    /** @test */
    public function notifyMustBeEmptyArrayByDefault()
    {
        // Arrange
        $object = new Activity();

        // Act
        $result = $object->activityNotify();

        // Assert
        $this->assertSame([], $result);
    }

    /** @test */
    public function extraDataMustBeEmptyArrayByDefault()
    {
        // Arrange
        $object = new Activity();

        // Act
        $result = $object->activityExtraData();

        // Assert
        $this->assertSame([], $result);
    }
}
