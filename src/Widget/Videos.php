<?php
namespace SK\VideoModule\Widget;

use Yii;
use yii\base\Widget;
use \yii\db\Expression;

use SK\VideoModule\Model\Video;

class Videos extends Widget
{
    private $cacheKey = 'video:widget:videos';
    /**
     * @var int Идентификатор текущей активной категории;
     */
    public $active_id = null;
    /**
     * @var string path to template
     */
    public $template;
    /**
     * @var string сортировка элементов
     * Можно использовать следующие параметры:
     * - date: string, по дате
     * - views: string, по просмотрам
     * - likes: string, по лайкам
     * - ctr: string, по цтр
     */
    public $order = 'ctr';
    /**
     * @var int Сколько роликов выводить
     */
    public $limit = 20;
    /**
     * @var string Ограничение по времени
     */
    public $timeAgoLimit = 'all-time';
    /**
     * @var int Время жизни кеша темплейта (html)
     */
    public $cacheDuration = 300;

    /**
     * Initializes the widget
     */
    public function init() {
        parent::init();

        if (!in_array($this->order, ['date', 'views', 'likes', 'ctr'])) {
            $this->order = 'ctr';
        }

        if (!in_array($this->timeAgoLimit, ['daily', 'weekly', 'monthly', 'yearly', 'all-time'])) {
            $this->timeAgoLimit = 'all-time';
        }
    }

    public function getViewPath()
    {
        return Yii::getAlias('@root/views/videos');
    }

    /**
     * Runs the widget
     *
     * @return string|void
     */
    public function run() {
        $cacheKey = $this->buildCacheKey();

        $html = Yii::$app->cache->get($cacheKey);

        if (false === $html) {
            $videos = $this->getVideos();

            if (empty($videos)) {
                return;
            }

            $html = $this->render($this->template, [
                'videos' => $videos,
            ]);

            Yii::$app->cache->set($cacheKey, $html, $this->cacheDuration);
        }

        return $html;
    }

    private function getVideos()
    {
        $query = Video::find()
            ->asThumbs();

        if ('all-time' === $this->timeAgoLimit) {
            $query->untilNow();
        } else {
            $query->rangedUntilNow($this->timeAgoLimit);
        }

        $query->onlyActive();

        if ('ctr' === $this->order) {
            $query->orderBy(['v.max_ctr' => SORT_DESC]);
        } elseif ('date' === $this->order) {
            $query->orderBy(['v.published_at' => SORT_DESC]);
        } else {
            $query->orderBy([$this->order => SORT_DESC]);
        }

        $result = $query
            ->limit($this->limit)
            ->asArray()
            ->all();

        if (count($result) < $this->limit) {
            $query->where(['and', ['<=', 'v.published_at', new Expression('NOW()')], ['status' => Video::STATUS_ACTIVE]]);

            $result = $query->all();
        }

        return $result;
    }

    private function buildCacheKey()
    {
        return "{$this->cacheKey}:{$this->order}:{$this->timeAgoLimit}:{$this->template}";
    }
}
