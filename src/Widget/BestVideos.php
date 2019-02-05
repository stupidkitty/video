<?php
namespace SK\VideoModule\Widget;

use Yii;
use yii\base\Widget;
use \yii\db\Expression;

use SK\VideoModule\Model\Video;

class BestVideos extends Widget
{
    private $cacheKey = 'video:widget:bestvideos';
    /**
     * @var int Идентификатор текущей активной категории;
     */
    public $active_id = null;
    /**
     * @var string path to template
     */
    public $template;
    /**
     * @var array|string сортировка элементов
     * Можно использовать следующие параметры:
     * - category_id: integer, идентификатор категории
     * - title: string, название
     * - position: integer, порядковый номер при ручной сортировке
     * - ctr: float, рассчитаный цтр по кликабельности категории.
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

        if (!in_array($this->order, ['views', 'likes', 'ctr'])) {
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

            //Yii::$app->cache->set($cacheKey, $html, $this->cacheDuration);
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
            $query->orderBy(['max_ctr' => SORT_DESC]);
        } else {
            $query->orderBy([$this->order => SORT_DESC]);
        }

        $result = $query
            ->limit($this->limit)
            ->asArray()
            ->all();

        if (count($result) < $this->limit) {
            $query->where(['and', ['<=', 'published_at', new Expression('NOW()')], ['status' => Video::STATUS_ACTIVE]]);

            $result = $query->all();
        }

        return $result;
    }

    private function buildCacheKey()
    {
        return "{$this->cacheKey}:{$this->order}:{$this->timeAgoLimit}:{$this->template}";
    }
}
