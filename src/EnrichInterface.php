<?php

namespace GetStream\Doctrine;

interface EnrichInterface
{
    /**
     * @param array $activities
     *
     * @return EnrichedActivity[]
     */
    public function enrichActivities(array $activities);

    /**
     * @param array $aggregatedActivities
     *
     * @return EnrichedActivity[]
     */
    public function enrichAggregatedActivities(array $aggregatedActivities);
}
