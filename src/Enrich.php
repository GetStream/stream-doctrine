<?php

namespace GetStream\Doctrine;

class Enrich implements EnrichInterface
{
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
        return [];
    }

    /**
     * @param array $references
     *
     * @return array
     */
    private function retrieveObjects(array $references)
    {
        return [];
    }

    /**
     * @param EnrichedActivity[] $activities
     * @param array $objects
     *
     * @return EnrichedActivity[]
     */
    public function injectObjects($activities, $objects)
    {
        return $activities;
    }
}
