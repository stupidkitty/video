<?php

namespace SK\VideoModule\Event;

use yii\base\Event;

final class UserSearchEvent extends Event
{
    public const NAME = 'user-search';

    public string $query = '';
}
