<?php
namespace SK\VideoModule\Widget;

use Yii;
use yii\base\Widget;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Provider\RelatedProvider;

class RelatedVideos extends Widget
{
    private $cacheKey = 'videos:widget:related_videos';
    /**
     * @var string путь к темплейту виджета
     */
    public $template;
    /**
     * @var integer $video_id
     */
    public $video_id;
    /**
     * @var array Коллекция массивов категорий.
     */
    public $videos;
    /**
     * @var array диапазон показа релейтедов
     * Пример: 'range' => [1, 5],
     */
    public $range;

    public $enable;

    /**
     * Initializes the widget
     */
    public function init() {
        parent::init();

        if (empty($this->video_id)) {
            return;
        }

        if (null === $this->enable) {
            $this->enable = Yii::$container->get(SettingsInterface::class)->get('related_enable', 0, 'videos');
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

        if (!$this->enable) {
            return;
        }

        $cacheKey = $this->buildCacheKey();

        $html = Yii::$app->cache->get($cacheKey);

        if (false === $html) {
            $videos = $this->getVideos();

            if (empty($videos)) {
                return;
            }

            if (\is_array($this->range)) {
                $rangeStart = ($this->range[0] > 0) ? $this->range[0] - 1 : 0 ;
                $rangeEnd = (!isset($this->range[1])) ? 1 : $this->range[1] ;
                $videos = \array_slice($videos, $rangeStart, $rangeEnd);
            }

            $html = $this->render($this->template, [
                'videos' => $videos,
            ]);

            Yii::$app->cache->set($cacheKey, $html, 600);
        }

        return $html;
    }

    /**
     * Получает "похожие" видео.
     *
     * @return array
     */
    private function getVideos()
    {
        if (null !== $this->videos) {
            return $this->videos;
        }

        $relatedProvider = new RelatedProvider;
        $this->videos = $relatedProvider->getModels($this->video_id);

        return $this->videos;
    }

    private function buildCacheKey()
    {
        $range = \implode(':', (array) $this->range);

        return "{$this->cacheKey}:{$this->video_id}:{$range}";
    }
}
