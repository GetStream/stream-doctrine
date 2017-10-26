<?php

namespace GetStream\Doctrine;

trait ActivityTrait
{
    abstract protected function activityId();

    public function activityObject()
    {
        return (static::class) .':'. $this->activityId();
    }

    public function activityForeignId()
    {
        return $this->activityObject();
    }

    public function activityNotify()
    {
        return [];
    }

    public function activityExtraData()
    {
        return [];
    }
}
