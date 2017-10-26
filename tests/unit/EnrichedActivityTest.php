<?php

namespace GetStream\Doctrine\Unit;

use GetStream\Doctrine\EnrichedActivity;
use PHPUnit\Framework\TestCase;

class EnrichedActivityTest extends TestCase
{
    /** @test */
    public function canLoopData()
    {
        // Arrange
        $data = [
            'foo' => 'bar',
        ];

        $object = new EnrichedActivity($data);

        // Act
        $result = [];
        foreach ($object as $key => $value) {
            $result[$key] = $value;
        }

        // Assert
        $this->assertSame($data, $result);
    }

    /** @test */
    public function arrayAccessImplementation()
    {
        // Arrange
        $object = new EnrichedActivity([]);

        // Act
        $object['foo'] = 'bar';
        $object['qux'] = 'baz';
        $hasQux = isset($object['qux']);
        $baz = $object['qux'];
        unset($object['qux']);
        $object[] = 'qux';

        // Assert
        $this->assertSame('baz', $baz);
        $this->assertTrue($hasQux);
        $this->assertSame(['foo' => 'bar', 0 => 'qux'], iterator_to_array($object->getIterator()));
    }

    /** @test */
    public function enrichment()
    {
        // Arrange
        $object = new EnrichedActivity([]);

        // Act
        $before = $object->enriched();
        $object->trackNotEnrichedField('foo', 'bar');
        $after = $object->enriched();

        $result = $object->getNotEnrichedData();

        // Assert
        $this->assertTrue($before);
        $this->assertFalse($after);
        $this->assertSame(['foo' => 'bar'], $result);
    }
}
