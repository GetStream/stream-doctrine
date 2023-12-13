<?php

namespace GetStream\Doctrine;

use Doctrine\ORM\Mapping as ORM;
use GetStream\Doctrine\Activity;

class ModelListener
{
    /**
     * @var FeedManagerInterface
     */
    private $feedManager;

    /**
     * @param FeedManagerInterface $feedManager
     */
    public function __construct(FeedManagerInterface $feedManager)
    {
        $this->feedManager = $feedManager;
    }

    /**
     * @ORM\PostPersist
     *
     * @param ActivityInterface $instance
     */
    public function activityCreated(ActivityInterface $instance)
    {
        $activity = $this->createActivity($instance);

        $this->feedManager
            ->getUserFeed($instance->activityActorId())
            ->addActivity($activity);
    }

    /**
     * @ORM\PreRemove
     *
     * @param ActivityInterface $instance
     */
    public function activityDeleting(ActivityInterface $instance)
    {
        $this->feedManager
            ->getUserFeed($instance->activityActorId())
            ->removeActivity($instance->activityForeignId(), true);
    }

    /**
     * @ORM\PostUpdate
     *
     * @param ActivityInterface $instance
     */
    public function activityUpdated(ActivityInterface $instance)
    {
        $activity = $this->createActivity($instance);
        $this->feedManager->getClient()->updateActivities([$activity]);
    }

    /**
     * @param ActivityInterface $instance
     *
     * @return array
     */
    private function createActivity(ActivityInterface $instance)
    {
        return array_merge([
            'actor' => $instance->activityActor(),
            'verb' => $instance->activityVerb(),
            'object' => $instance->activityObject(),
            'foreign_id' => $instance->activityForeignId(),
            'time' => $instance->activityTime()->format(DATE_ISO8601),
            'to' => $instance->activityNotify(),
        ], $instance->activityExtraData());
    }
}
