<?php

namespace GetStream\Doctrine;

use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Events;
use GetStream\Doctrine\ReactionInterface;
use GetStream\Stream\Client;

class ReactionSubscriber implements EventSubscriber
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * Returns an array of events this subscriber wants to listen to.
     *
     * @return string[]
     */
    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::postUpdate,
            Events::preRemove,
        ];
    }

    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ReactionInterface) {
            if (null === ($activityId = $entity->getReactionActivityId())) {
                $activityId = $this->findActivityId($entity->getReactionActivityForeignId(), $entity->getReactionActivityTime());
            }

            if (null === $activityId) {
                return;
            }

            $getStreamReaction = $this->client->reactions()
                ->add($entity->getReactionKind(), $activityId, $entity->getUserId(), $entity->getReactionData(), $entity->getReactionTargets());

            $entity->setReactionId($getStreamReaction['id']);
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ReactionInterface && null !== $entity->getReactionId()) {
            $this->client->reactions()->update($entity->getReactionId(), $entity->getReactionData(), $entity->getReactionTargets());
        }
    }

    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ReactionInterface && null !== $entity->getReactionId()) {
            $this->client->reactions()->delete($entity->getReactionId());
        }
    }

    private function findActivityId(string $activityForeignId, \DateTimeImmutable $activityTime)
    {
        $activities = $this->client->getActivities(null, [[$activityForeignId, $activityTime]]);

        if (empty($activities) || empty($activities['results'])) {
            return null;
        }

        return $activities['results'][0]['id'];
    }
}
