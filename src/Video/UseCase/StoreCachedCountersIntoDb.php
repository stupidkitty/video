<?php

namespace SK\VideoModule\Video\UseCase;

use Exception;
use SK\VideoModule\Model\Video;
use yii\db\Connection;

class StoreCachedCountersIntoDb
{
    private CountersCache $countersCache;
    private Connection $db;

    private array $allowedCounters = [
        'views' => 1,
        'likes' => 1,
        'dislikes' => 1
    ];

    public function __construct(CountersCache $countersCache)
    {
        $this->countersCache = $countersCache;
        $this->db = Video::getDb();
    }

    /**
     * @throws \Throwable
     */
    public function store()
    {
        $this->db->transaction(function () {
            $this->storeViews();
            $this->storeLikes();
            $this->storeDislikes();
        });
    }

    /**
     * Store cached views
     *
     * @return void
     * @throws Exception
     */
    private function storeViews()
    {
        $views = $this->countersCache->getWithRemovalViewed();
        $this->updateCounter('views', $views);
    }

    /**
     * Store cached likes
     *
     * @return void
     * @throws Exception
     */
    private function storeLikes()
    {
        $likes = $this->countersCache->getWithRemovalLiked();
        $this->updateCounter('likes', $likes);
    }

    /**
     * Store cached dislikes
     *
     * @return void
     * @throws Exception
     */
    private function storeDislikes()
    {
        $dislikes = $this->countersCache->getWithRemovalDisliked();
        $this->updateCounter('dislikes', $dislikes);
    }

    /**
     * Update a counter
     *
     * The $data must be an array like:
     * ```
     * [
     *     (int) video_id => (int) counter
     *     ...
     * ]
     * ```
     * @see https://stackoverflow.com/a/19033152
     * @param string $counter
     * @param array $data
     * @return void
     * @throws \yii\db\Exception
     * @throws Exception
     */
    private function updateCounter(string $counter, array $data): void
    {
        if (!$this->isAllowedCounter($counter)) {
            throw new Exception("Counter $counter is not allowed");
        }

        if (\count($data) === 0) {
            return;
        }

        $i = 0;
        $subQuery = '';
        foreach ($data as $id => $count) {
            if ($i === 0) {
                $subQuery .= "SELECT {$id} as `video_id`, {$count} as `_{$counter}`";
            } else {
                $subQuery .= " UNION ALL SELECT {$id}, {$count}";
            }

            $i++;
        }

        $sql = "
            UPDATE `videos` as `v`
            JOIN (
                {$subQuery}
            ) `x` ON `v`.`video_id` = `x`.`video_id`
            SET `{$counter}` = `{$counter}` + `_{$counter}`;
        ";

        $this->db
            ->createCommand($sql)
            ->execute();
    }

    /**
     * Checks counter is allowed
     *
     * @param string $counter
     * @return bool
     */
    private function isAllowedCounter(string $counter): bool
    {
        return isset($this->allowedCounters[$counter]);
    }
}
