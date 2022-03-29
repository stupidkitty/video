<?php

namespace SK\VideoModule\Api\Request;

use SK\VideoModule\Widget\Videos;
use yii\web\Request;

/**
 * Class UpdateVideosDto
 * Intended for batch video updates
 */
class UpdateVideosDto
{
    /**
     * Массив должен иметь структуру каждого элемент примерно следующего вида:
     * ```
     * [
     *    "id": (int) // required ,
     *    "slug": (?string),
     *    "title": (string),
     *    "description": (?string),
     *    "searchField": (?string),
     *    "orientation": (int),
     *    "duration": (int),
     *    "videoPreview": (?string),
     *    "embed": (?string),
     *    "onIndex": (bool),
     *    "likes": (int),
     *    "dislikes": (int),
     *    "isHd": (bool),
     *    "noindex": (bool),
     *    "nofollow": (bool),
     *    "views": (int),
     *    "publishedAt": (?string),
     *    "custom1": (?string),
     *    "custom2": (?string),
     *    "custom3": (?string),
     *    "poster": {
     *        "id": (int),
     *        "filepath": (string),
     *        "sourceUrl": (string),
     *        "status": (int),
     *    },
     *    "categories": [{
     *        "id": (int)
     *    }],
     * ]
     * ```
     * @var array[][] $videos Raw data for update videos
     */
    public array $videos = [];

    /**
     * Creating the instance from request
     *
     * @param Request $request
     * @return static
     */
    public static function createFromRequest(Request $request): self
    {
        $o = new static();

        $videos = $request->post('videos', []);

        if (\is_array($videos)) {
            $o->videos = $request->post('videos', []);
        }

        return $o;
    }
}
