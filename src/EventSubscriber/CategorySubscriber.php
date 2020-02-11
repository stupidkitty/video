<?php
namespace SK\VideoModule\EventSubscriber;

use SK\VideoModule\Model\VideosCategories;

final class CategorySubscriber
{
    /**
     * Событие должно подключаться к обновляемому объекту.
     */
    public static function onCreate($event)
    {
        $event->sender->updated_at = gmdate('Y-m-d H:i:s');
        $event->sender->created_at = gmdate('Y-m-d H:i:s');
    }

    /**
     * Событие должно подключаться к обновляемому объекту.
     */
    public static function onUpdate($event)
    {
        $event->sender->updated_at = gmdate('Y-m-d H:i:s');
    }

    /**
     * Событие должно подключаться к удаляемому объекту.
     */
    public static function onDelete($event)
    {
        $category = $event->sender;

        VideosCategories::deleteAll(['category_id' => $category->getId()]);
    }
}
