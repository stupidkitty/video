<?php
namespace SK\VideoModule;

use SK\VideoModule\Event\UserSearchEvent;
use SK\VideoModule\EventSubscriber\VideoSubscriber;
use SK\VideoModule\Fetcher\VideoFetcher;
use Yii;

$container = Yii::$container;

$container->set(Rotator\UserBehaviorHandler::class);
$container->set(VideoFetcher::class);

Yii::$app->on(UserSearchEvent::NAME, [VideoSubscriber::class, 'onUserSearch']);
