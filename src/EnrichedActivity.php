<?php

namespace GetStream\Doctrine;

use ArrayAccess;
use ArrayIterator;
use IteratorAggregate;
use Traversable;

class EnrichedActivity implements ArrayAccess, IteratorAggregate
{
    /**
     * @var array
     */
    private $activityData = [];

    /**
     * @var array
     */
    private $notEnrichedData = [];

    /**
     * @param array $activityData
     */
    public function __construct(array $activityData)
    {
        $this->activityData = $activityData;
    }

    /**
     * @param string $field
     * @param mixed $value
     */
    public function trackNotEnrichedField($field, $value)
    {
        $this->notEnrichedData[$field] = $value;
    }

    /**
     * @return array
     */
    public function getNotEnrichedData()
    {
        return $this->notEnrichedData;
    }

    /**
     * @return bool
     */
    public function enriched()
    {
        return empty($this->notEnrichedData);
    }

    // ArrayAccess implementation methods

    /**
     * @param mixed $offset
     * @param mixed $value
     */
    public function offsetSet($offset, $value)
    {
        if (is_null($offset)) {
            $this->activityData[] = $value;
        } else {
            $this->activityData[$offset] = $value;
        }
    }

    /**
     * @param mixed $offset
     *
     * @return bool
     */
    public function offsetExists($offset)
    {
        return isset($this->activityData[$offset]);
    }

    /**
     * @param mixed $offset
     */
    public function offsetUnset($offset)
    {
        unset($this->activityData[$offset]);
    }

    /**
     * @param mixed $offset
     *
     * @return mixed|null
     */
    public function offsetGet($offset)
    {
        return isset($this->activityData[$offset]) ? $this->activityData[$offset] : null;
    }

    /**
     * Retrieve an external iterator
     *
     * @return Traversable
     */
    public function getIterator()
    {
        return new ArrayIterator($this->activityData);
    }
}
