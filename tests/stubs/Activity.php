<?php

namespace GetStream\Doctrine\Stubs;

use Cake\Chronos\Chronos;
use GetStream\Doctrine\ActivityInterface;
use GetStream\Doctrine\ActivityTrait;

class Activity implements ActivityInterface
{
    use ActivityTrait;

    /**
     * @return string
     */
    protected function activityId()
    {
        return '1';
    }

    /**
     * @return string
     */
    public function activityActorId()
    {
        return '2';
    }

    /**
     * @return string
     */
    public function activityActor()
    {
        return '\User:2';
    }

    /**
     * @return string
     */
    public function activityVerb()
    {
        return 'like';
    }

    /**
     * @return \DateTimeImmutable
     */
    public function activityTime()
    {
        return Chronos::now();
    }
}
