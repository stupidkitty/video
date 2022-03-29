<?php

namespace SK\VideoModule\Api\Request;

use yii\web\Request;

class GetVideosFilter
{
    public array $fieldNames = [];
    public array $selectionCriteria = [];
    public array $page = [
        'offset' => 0,
        'limit' => 50
    ];

    public static function createFromRequest(Request $request): self
    {
        $o = new static();

        $fieldNames = $request->post('fieldNames', []);

        if (\is_array($fieldNames)) {
            $o->fieldNames = $fieldNames;
        }

        $selectionCriteria = $request->post('selectionCriteria', []);

        if (\is_array($selectionCriteria)) {
            $o->selectionCriteria = $selectionCriteria;
        }

        $page = $request->post('page', []);

        if (isset($page['offset'])) {
            $o->page['offset'] = (int) $page['offset'];
        }

        if (isset($page['limit']) && (int) $page['limit'] <= 500) {
            $o->page['limit'] = (int) $page['limit'];
        }

        return $o;
    }
}
