<?php

namespace GetStream\Doctrine;

interface ActivityInterface
{
    /**
     * Like activityActor but without the colon and the namespacing which is often `user:`.
     * Example `1` (from `user:1`).
     *
     * @return string
     */
    public function activityActorId();

    /**
     * Returns a reference to the user performed the activity.
     * Example: `user:eric liked tweet:5`, in this case the 'user:eric' is the actor.
     *
     * @return string
     */
    public function activityActor();

    /**
     * Example: `user:eric liked tweet:5`, in this case, the verb is 'like', form 'to like'.
     *
     * @return string
     */
    public function activityVerb();

    /**
     * Returns a reference to the object that the actor did the verb on.
     * Example: `user:eric liked tweet:5`, in this sentence tweet:5 is the object.
     *
     * @return string
     */
    public function activityObject();

    /**
     * Returns the foreign ID of an activity, as a reference to your internal identification of the activity.
     * Example: `user:eric liked tweet:5`, and you stored the 'like' event with like:7.
     *
     * See: https://getstream.io/docs/#foreign-ids
     *
     * @return string
     */
    public function activityForeignId();

    /**
     * Returns the time when the activity happened or is created at.
     *
     * @return \DateTimeImmutable
     */
    public function activityTime();

    /**
     * Returns an array of feed slugs.
     *
     * @return array
     */
    public function activityNotify();

    /**
     * Returns extra data which could be used by stream for example custom ranking.
     *
     * @return array
     */
    public function activityExtraData();
}
