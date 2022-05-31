<?php

namespace GetStream\Doctrine;

use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\ORM\EntityManagerInterface;

class Enrich implements EnrichInterface
{
    /**
     * @var array
     */
    private $enrichingFields = ['actor', 'object'];

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    /**
     * @param EntityManagerInterface $entityManager
     */
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param array $fields
     *
     * @return $this
     */
    public function setEnrichingFields(array $fields)
    {
        $this->enrichingFields = $fields;

        return $this;
    }

    /**
     * @param array $activities
     *
     * @return EnrichedActivity[]
     */
    public function enrichActivities(array $activities)
    {
        if (empty($activities)) {
            return [];
        }

        $activities = $this->wrapActivities($activities);
        $references = $this->collectReferences($activities);
        $objects = $this->retrieveObjects($references);

        return $this->injectObjects($activities, $objects);
    }

    /**
     * @param array $aggregatedActivities
     *
     * @return EnrichedActivity[]
     */
    public function enrichAggregatedActivities(array $aggregatedActivities)
    {
        if (empty($aggregatedActivities)) {
            return [];
        }

        $allActivities = [];

        foreach ($aggregatedActivities as &$aggregated) {
            $activities = $this->wrapActivities($aggregated['activities']);
            $allActivities = array_merge($allActivities, $activities);

            $aggregated['activities'] = $activities;
        }

        $references = $this->collectReferences($allActivities);
        $objects = $this->retrieveObjects($references);
        $this->injectObjects($allActivities, $objects);

        return $aggregatedActivities;
    }

    /**
     * @param array $activities
     *
     * @return EnrichedActivity[]
     */
    private function wrapActivities(array $activities)
    {
        return array_map(function (array $activity) {
            return new EnrichedActivity($activity);
        }, $activities);
    }

    /**
     * @param EnrichedActivity[] $activities
     *
     * @return array
     */
    private function collectReferences(array $activities)
    {
        $references = [];

        foreach ($activities as $activity) {
            foreach ($activity as $field => $value) {
                if ($value === null) {
                    continue;
                }

                if (!in_array($field, $this->enrichingFields)) {
                    continue;
                }

                list($type, $identifier) = explode(':', $value);
                $references[$type][] = $identifier;
            }
        }

        return $references;
    }

    /**
     * @param array $references
     *
     * @return array
     */
    private function retrieveObjects(array $references)
    {
        $objects = [];

        foreach (array_keys($references) as $type) {
            $identifiers = array_unique($references[$type]);

            $result = $this->entityManager
                ->getRepository($type)
                ->matching(
                    Criteria::create()
                    ->where(new Comparison('id', Comparison::IN, $identifiers))
                )->getValues();

            $keys = array_map(function ($item) {
                return $item->id();
            }, $result);

            $objects[$type] = array_combine($keys, $result);
        }

        return $objects;
    }

    /**
     * @param EnrichedActivity[] $activities
     * @param array $objects
     *
     * @return EnrichedActivity[]
     */
    public function injectObjects($activities, $objects)
    {
        foreach ($activities as &$activity) {
            foreach ($this->enrichingFields as $field) {
                if (!isset($activity[$field])) {
                    continue;
                }

                $value = $activity[$field];
                list($type, $identifier) = explode(':', $value);

                if (!isset($objects[$type], $objects[$type][$identifier])) {
                    $activity->trackNotEnrichedField($type, $identifier);
                    continue;
                }

                $activity[$field] = $objects[$type][$identifier];
            }
        }

        return $activities;
    }
}
