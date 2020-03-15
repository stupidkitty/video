<?php
namespace SK\VideoModule;

use Yii;

$container = Yii::$container;

$container->set(Rotator\UserBehaviorHandler::class);
$container->set(Csv\CategoryCsvHandler::class);
