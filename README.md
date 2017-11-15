# Stream Doctrine

[![Build Status](https://travis-ci.org/GetStream/stream-doctrine.svg?branch=master)](https://travis-ci.org/GetStream/stream-doctrine)
[![Code Coverage](https://scrutinizer-ci.com/g/GetStream/stream-doctrine/badges/coverage.png?b=master)](https://scrutinizer-ci.com/g/GetStream/stream-doctrine/)
[![Code Quality](https://scrutinizer-ci.com/g/GetStream/stream-doctrine/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/GetStream/stream-doctrine/)
[![Latest Stable Version](https://poser.pugx.org/get-stream/stream-doctrine/v/stable)](https://packagist.org/packages/get-stream/stream-doctrine)
[![Total Downloads](https://poser.pugx.org/get-stream/stream-doctrine/downloads)](https://packagist.org/packages/get-stream/stream-doctrine)
[![License](https://poser.pugx.org/get-stream/stream-doctrine/license)](https://packagist.org/packages/get-stream/stream-doctrine)

[stream-doctrine](https://github.com/GetStream/stream-doctrine) is a package that integrates you Doctrine entities with [Stream](https://getstream.io/).

You can sign up for a Stream account at [https://getstream.io/get_started](https://getstream.io/get_started).

Note there is also a lower level [PHP - Stream integration](https://github.com/getstream/stream-php) library which is suitable for all PHP applications.

## Build Activity Streams, News Feeds, and More

![](https://dvqg2dogggmn6.cloudfront.net/images/mood-home.png)

You can build:

* Activity Streams - like the one seen on GitHub
* A Twitter-like feed
* Instagram / Pinterest Photo Feeds
* Facebook-style newsfeeds
* A Notification System
* Lots more...

## Installation

### Composer

```
composer require get-stream/stream-doctrine
```

Composer will install our latest version automatically.

### PHP compatibility

Current releases require PHP `5.6` or higher, and depend on `doctrine/orm` version `2.5` or higher.

See the [Travis configuration](.travis.yml) for details of how it is built and tested against different PHP versions.

## GetStream.io Dashboard

Now, login to [GetStream.io](https://getstream.io) and create an application in the dashboard.

Retrieve the API key, API secret, and API app id, which are shown in your dashboard.

## Stream-Doctrine setup

### Features of Stream-Doctrine

#### Entity integration

Stream-Doctrine provides instant integration with Doctrine entities - implementing the ```GetStream\Doctrine\Activity``` interface and adding an EntityListener will give you automatic tracking of your models to user feeds.

For example:

```php
/**
 * @ORM\Table(name="pins")
 * @ORM\EntityListeners({"\GetStream\Doctrine\ModelListener"})
 */
class Pin implements Activity
{
    use ActivityTrait;
}
```

Every time a Pin is created it will be stored in the feed of the user that created it, and when a Pin instance is deleted then it will get removed as well.

Automatically!

### Activity Fields

Models are stored in feeds as activities. An activity is composed of at least the following data fields: **actor**, **verb**, **object**, **time**. You can also add more custom data if needed.

**object** is a reference to the entity instance itself
**actor** is a reference to the user attribute of the instance
**verb** is a string representation of the class name
**time** is a DateTimeInterface object of when the activity happened. Mostly this will be the `created_at` or similar attribute of the entity.

A few assumptions are made on the entity class in order to be able to implement the ActivityInterface:

1. the Entity has an identifier (which goes for every entity).
2. the Entity has a *-to-one relationship to some user or "actor".
3. the Entity has a timestamp column (`created_at` for example).

You can change how a entity instance is stored as activity by implementing specific methods as explained later.

An example of a complete Activity typed entity is shown below:

```php
class Pin implements ActivityInterface
{
    use ActivityTrait;

    /**
     * Each pin is created by someone.
     *
     * @var User
     *
     * @ORM\ManyToOne(targetEntity="User", fetch="EXTRA_LAZY")
     * @ORM\JoinColumn(name="creator_id", referencedColumnName="id")
     */
    private $creator;
    
    /**
     * @ORM\Column(type="datetime_immutable", name="created_at")
     */
    private $createdAt;

    /**
     * @return string
     */
    public function activityVerb()
    {
        return 'pin';
    }

    /**
     * @return string
     */
    protected function activityId()
    {
        return $this->id;
    }

    /**
     * @return string
     */
    public function activityActorId()
    {
        return $this->creator->id();
    }

    /**
     * @return string
     */
    public function activityActor()
    {
        return User::class.':'.$this->activityActorId();
    }

    /**
     * @return DateTimeInterface
     */
    public function activityTime()
    {
        return $this->createdAt;
    }
```

You can change every `activity*` method according to your needs or entity implementation.

### Activity Extra Data

Often, you'll want to store more data than just the basic fields, for use with custom ranking or aggregation, for example.
You achieve this by implementing the ```activityExtraData``` method in the entity.

NOTE: The data will be serialized using the json_encode function, so it needs to be valid input for that.

```php
class Tweet implements ActivityInterface
{
    use ActivityTrait;

    public function activityExtraData()
    {
        return ['is_retweet' => $this->isRetweet()];
    }
}
```

## Feed Manager

`stream-doctrine` comes with a FeedManager class (implementing FeedManagerInterface) that helps with all common feed operations.
The FeedManager needs a configured `GetStream\Stream\Client` object when instantiating it.

## Pre-Bundled Feeds

To get you started the manager has feeds pre configured. You can add more feeds if your application needs it. The three feeds are divided in three categories.

### User Feed:

The user feed stores all activities for a user. Think of it as your personal Facebook page. You can easily get this feed from the manager.

```php
/** @var $feed GetStream\Stream\Feed */
$feed = $feedManager->getUserFeed($userId);
```

### News Feed:

The news feeds store the activities from the people you follow.
You can setup the names of the newsfeeds using:

```php
$feedManager->setNewsFeeds(['timeline', 'timeline_aggregated']);
```

Then you can access those feeds in an ArrayAccess kind of way:

```php
$timelineFeed = $feedManager->getNewsFeed($userId)['timeline'];
$aggregatedTimelineFeed = FeedManager::getNewsFeed($userId)['timeline_aggregated'];
```

Both user feed and news feeds are shorthand methods for:

```php
$feed = $feedManager->getFeed('user', $userId);
$feed = $feedManager->getFeed('timeline', $userId);
$feed = $feedManager->getFeed('timeline_aggregated', $userId);
```

### Notification Feed:
The notification feed can be used to build notification functionality.

![Notification feed](http://feedly.readthedocs.org/en/latest/_images/fb_notification_system.png)

Below we show an example of how you can read the notification feed.

```php
$notificationFeed = $feedManager->getNotificationFeed($user->id);
```

By default the notification feed will be empty. You can specify which users to notify when your model gets created. In the case of a retweet you probably want to notify the user of the parent tweet.

```php
class Tweet implements ActivityInterface
{
    use ActivityTrait;

    /**
     * @return array
     */
    public function activityNotify()
    {
        if ($this->isRetweet()) {
            return ['notification:'.$this->parent()->user()->id()];
        }

        return [];
    }
```

Another example would be following a user. You would commonly want to notify the user which is being followed.

```php
class Follow implements ActivityInterface
{
    use ActivityTrait;

    /**
     * @return array
     */
    public function activityNotify()
    {
        return ['notification:'.$this->target()->id()];
    }
}
```

## Follow Feed

To create the news feeds you need to notify the system about follow relationships.
The manager comes with methods to let a user's news feeds follow another user's feed.
This code lets the current user's news feeds follow the target_user's personal feed.

```
/** @var GetStream\Doctrine\FeedManager $manager */
$manager->setNewsFeeds(['timeline', 'timeline_aggregated']);
$manager->followUser($userId, $targetId);

// Same as:
$targetFeed = $client->feed('user', $targetId);
$feed1 = $client->feed('timeline', $userId);
$feed2 = $client->feed('timeline_aggregated', $userId);

$feed1->follow($targetFeed->getSlug(), $targetFeed->getId());
$feed2->follow($targetFeed->getSlug(), $targetFeed->getId());
```

## Displaying the Newsfeed

### Activity Enrichment

When you read data from feeds with activities created by `stream-doctrine`, an activity will look like this:

```json
{
    "actor": "User:1",
    "verb": "like",
    "object": "Like:42"
}
```

This is far from ready for usage in your template.
We call the process of loading references from the database: enrichment.
An example is shown below.

```php
use GetStream\Doctrine\Enrich;

$feed = $feedManager->getUserFeed($userId);
$activities = $feed->getActivities(0, 25)['results'];

$enricher = new Enrich();
$activities = $enricher->enrichActivities($activities);
```

### Customizing Enrichment

Sometimes you'll want to customize how enrichment works.
If you store references to model instances as custom fields (using the `activityExtraData()` method)
you can use the `Enrich` class to take care of it for you:

```php
class Pin implements ActivityInterface
{
    use ActivityTrait;

    public function activityExtraData()
    {
        return ['parent_tweet' => self::class.':'.$this->parent()->id()];
    }
}
```

Tell the enricher to enrich the `parent_tweet` field into a full entity object:

```php
$enricher = (new Enrich()->setEnrichingFields(['actor', 'object', 'parent_tweet']));
$activities = $feed->getActivities(0,25)['results'];
$activities = $enricher->enrichActivities($activities);
```

### Full documentation and Low level APIs access

When needed you can also use the [low level PHP API](https://github.com/getstream/stream-php) directly. Documentation is available at the [Stream website](https://getstream.io/docs/?language=php).

```
/** @var $client GetStream\Stream\Client */
$client = $feedManager->getClient();
```

### Copyright and License Information

Copyright (c) 2014-2017 Stream.io Inc, and individual contributors. All rights reserved.

See the file "LICENSE" for information on the history of this software, terms & conditions for usage, and a DISCLAIMER OF ALL WARRANTIES.
