<?php

namespace GetStream\Doctrine;

interface ReactionInterface
{
    public function getReactionId(): string;

    public function setReactionId(string $reactionId);

    public function getReactionKind(): string;

    public function getReactionActivityId(): string;

    public function getReactionActivityForeignId(): string;

    public function getReactionActivityTime(): \DateTimeImmutable;

    public function getUserId(): string;

    public function getReactionData(): ?array;

    public function getReactionTargets(): ?array;
}