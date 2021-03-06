<?php
namespace SK\VideoModule\Query;

use yii\db\ActiveQuery;
use yii\db\Expression;

use SK\VideoModule\Model\Video;

class VideoQuery extends ActiveQuery
{
    public function asThumbs()
    {
        return $this->select([
                'v.video_id',
                'v.image_id',
                'v.slug',
                'v.title',
                'v.orientation',
                'v.video_preview',
                'v.duration',
                'v.likes',
                'v.dislikes',
                'v.comments_num',
                'v.is_hd',
                'v.noindex',
                'v.nofollow',
                'v.views',
                'v.template',
                'v.published_at'
            ])
            ->alias('v')
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1'])
                    ->where(['enabled' => 1]);
            }])
            ->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }]);
    }

    /**
     * Добавляет к запросу условие "только активные"
     *
     * @return \yii\db\ActiveQuery
     */
    public function onlyActive()
    {
        return $this->andWhere(['v.status' => Video::STATUS_ACTIVE]);
    }

    /**
     * Добавляет к запросу условие "только активные"
     *
     * @return \yii\db\ActiveQuery
     */
    public function onlyHd()
    {
        return $this->andWhere(['v.is_hd' => 1]);
    }

    /**
     * Добавляет к запросу условие "до текущего времени"
     *
     * @return \yii\db\ActiveQuery
     */
    public function untilNow()
    {
        return $this->andWhere(['<=', 'v.published_at', new Expression('NOW()')]);
    }

    /**
     * Добавляет к запросу условие "между текущей датой и заданной"
     *
     * @return \yii\db\ActiveQuery
     */
    public function rangedUntilNow($rangeStart)
    {
        $timeagoExpression = $this->getTimeagoExpression($rangeStart);

        return $this->andWhere(['between', 'v.published_at', new Expression($timeagoExpression), new Expression('NOW()')]);
    }

    /**
     * Подключает связанные модели для страницы просмотра видео.
     *
     * @return \yii\db\ActiveQuery
     */
    public function withViewRelations()
    {
        return $this->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }])
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1'])
                    ->where(['enabled' => 1]);
            }]);
    }

    /**
     * Подключает связанные модели для страницы просмотра видео.
     *
     * @return \yii\db\ActiveQuery
     */
    public function whereIdOrSlug($identify)
    {
        if (is_integer($identify)) {
            return $this->where(['v.video_id' => $identify]);
        }

        return $this->where(['v.slug' => $identify]);
    }

    /**
     * Кеширует подсчет элементов датасета. Кастыль :(
     *
     * @return integer
     */
    public function cachedCount()
    {
        $count = $this
            ->cache(300)
            ->count();

        $this->noCache();

        return $count;
    }

    /**
     * Возвращает выражение для первого значения в выборке по интервалу времени.
     * Значения: daily, weekly, monthly, early, all_time
     *
     * @param string $time Ограничение по времени.
     *
     * @return string
     */
    protected function getTimeagoExpression($time)
    {
        $times = [
            'daily' => '(NOW() - INTERVAL 1 DAY)',
            'weekly' => '(NOW() - INTERVAL 1 WEEK)',
            'monthly' => '(NOW() - INTERVAL 1 MONTH)',
            'yearly' => '(NOW() - INTERVAL 1 YEAR)',
        ];

        if (isset($times[$time])) {
            return $times[$time];
        }

        return $times['yearly'];
    }
}
