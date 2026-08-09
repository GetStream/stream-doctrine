<?php

namespace GetStream\Doctrine\Stubs;

use Cake\Chronos\Chronos;
use GetStream\Doctrine\ActivityInterface;
use GetStream\Doctrine\ActivityTrait;

class ActivityWithExtraData implements ActivityInterface
{
    use ActivityTrait;

    private $extraData = [
        'tags' => ['tag1', 'tag2'],
        'pinned' => false,
    ];

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

    public function activityExtraData()
    {
        return $this->extraData;
    }

    /**
     * @param array $extraData
     */
    public function setExtraData(array $extraData = [])
    {
        $this->extraData = $extraData;
    }
}
