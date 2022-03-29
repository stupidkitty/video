<?php
namespace SK\VideoModule;

use SK\VideoModule\Fetcher\VideoFetcher;
use Yii;

$container = Yii::$container;

$container->set(Rotator\UserBehaviorHandler::class);
$container->set(VideoFetcher::class);
