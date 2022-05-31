<?php

namespace GetStream\Doctrine;

interface ReactionInterface
{
    /**
     * @return string
     */
    public function getReactionId();

    /**
     * @param string $reactionId
     *
     * @return $this
     */
    public function setReactionId($reactionId);

    /**
     * @return string
     */
    public function getReactionKind();

    /**
     * @return string
     */
    public function getReactionActivityId();

    /**
     * @return string
     */
    public function getReactionActivityForeignId();

    /**
     * @return \DateTimeImmutable
     */
    public function getReactionActivityTime();

    /**
     * @return string
     */
    public function getUserId();

    /**
     * @return array|null
     */
    public function getReactionData();

    /**
     * @return array|null
     */
    public function getReactionTargets();
}
