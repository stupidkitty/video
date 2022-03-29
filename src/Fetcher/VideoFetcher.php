<?php

namespace SK\VideoModule\Fetcher;

use DateTimeInterface;
use SK\VideoModule\Api\Request\GetVideosFilter;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Query\VideoQuery;

class VideoFetcher
{
    public function fetch(GetVideosFilter $filter): array
    {
        $query = $this->buildQuery($filter);

        $result = [
            'items' => [],
            'total' => (int) $query->count()
        ];

        $fieldsHash = \array_flip($filter->fieldNames);
        $fieldsHash['id'] = 0;

        $result['items'] = \array_map(function ($video) use ($fieldsHash) {
            $item = [
                'id' => (int) $video['video_id'],
                'slug' => (string) $video['slug'],
                'title' =>  (string) $video['title'],
                'description' => (string) $video['description'],
                'searchField' => (string) $video['search_field'],
                'orientation' => (int) $video['orientation'],
                'duration' => (int) $video['duration'],
                'videoPreview' => (string) $video['video_preview'],
                'embed' => (string) $video['embed'],
                'onIndex' => (int) $video['on_index'],
                'likes' => (int) $video['likes'],
                'dislikes' => (int) $video['dislikes'],
                'commentsNum' => (int) $video['comments_num'],
                'isHd' => (int) $video['is_hd'],
                'noindex' => (int) $video['noindex'],
                'nofollow' => (int) $video['nofollow'],
                'views' => (int) $video['views'],
                'publishedAt' => \gmdate(DateTimeInterface::ATOM, \strtotime($video['published_at'])),
                'custom1' => (string) $video['custom1'],
                'custom2' => (string) $video['custom2'],
                'custom3' => (string) $video['custom3'],
                'maxCtr' => (float) $video['max_ctr'],
                'updatedAt' => \gmdate(DateTimeInterface::ATOM, \strtotime($video['updated_at'])),
                'createdAt' => \gmdate(DateTimeInterface::ATOM, \strtotime($video['created_at'])),
            ];

            if (isset($fieldsHash['poster'])) {
                $item['poster'] = $video['poster'] ?? null;
            }

            if (isset($fieldsHash['categories'])) {
                $item['categories'] = $video['categories'] ?? [];
            }

            return \array_intersect_key($item, $fieldsHash);
        }, $query->all());

        return $result;
    }

    private function buildQuery(GetVideosFilter $filter): VideoQuery
    {
        $query = Video::find()
            ->asArray()
            ->offset($filter->page['offset'] ?? 0)
            ->limit($filter->page['limit'] ?? 50);

        if (\in_array('categories', $filter->fieldNames)) {
            $query->with('categories');
        }

        if (\in_array('poster', $filter->fieldNames)) {
            $query->with('poster');
        }

        if (isset($filter->selectionCriteria['ids']) && \is_array($filter->selectionCriteria['ids']) && \count($filter->selectionCriteria['ids']) !== 0) {
            $query->andWhere(['video_id' => $filter->selectionCriteria['ids']]);
        }

        return $query;
    }
}
