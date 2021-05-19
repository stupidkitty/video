<?php
namespace SK\VideoModule\Widget;

use Yii;
use yii\base\Widget;
use SK\VideoModule\Model\Category;
use yii\caching\TagDependency;

class Categories extends Widget
{
    /**
     * @var int Идентификатор текущей активной категории;
     */
    public $active_id = 0;

    /**
     * @var string path to template
     */
    public $template;

    /**
     * Можно использовать следующие параметры:
     * - id: integer, идентификатор категории
     * - title: string, название
     * - position: integer, порядковый номер при ручной сортировке
     * - clicks: integer, клики по категориям тумб.
     *
     * @var array|string сортировка элементов
     */
    public $order = 'title';

    /**
     * Лимит вывода категорий.
     *
     * @var integer
     */
    public $limit;

    /**
     * Группирует категории по первой букве
     *
     * @var boolean
     */
    public $groupByFirstLetter = false;

    /**
     * Включает кеш виджета.
     *
     * @var boolean
     */
    public $enableCache = true;

    /**
     * Время жизни кеша темплейта (html)
     *
     * @var integer
     */
    public $cacheDuration = 300;

    /**
     * @var array Коллекция массивов категорий.
     */
    public $items = [];

    private $cache;

    private $defaultCacheKey = 'video:widget:categories:';

    /**
     * Initializes the widget
     */
    public function init() {
        parent::init();

        $this->cache = Yii::$app->cache;

        if (!in_array($this->order, ['id', 'title', 'position', 'clicks'])) {
            $this->order = 'title';
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
    public function run()
    {
        $cacheKey = $this->buildCacheKey();

        $html = $this->isCacheEnabled() ?  $this->cache->get($cacheKey) : false;

        if (false === $html) {
            $categories = $this->getItems();

            if (empty($categories)) {
                return;
            }

            $html = $this->render($this->template, [
                'categories' => $categories,
                'active_id' => $this->active_id,
            ]);

            if ($this->isCacheEnabled()) {
                $this->cache->set($cacheKey, $html, $this->cacheDuration, new TagDependency(['tags' => 'categories']));
            }
        }

        return $html;
    }

    private function getItems()
    {
        $order = null;

        if ('title' === $this->order) {
            $order = ['title' => SORT_ASC];
        } elseif ('position' === $this->order) {
            $order = ['position' => SORT_ASC];
        } elseif ('id' === $this->order) {
            $order = ['category_id' => SORT_ASC];
        } elseif ('clicks' === $this->order) {
            $order = ['last_period_clicks' => SORT_DESC];
        }

        $query = Category::find()
            ->select(['category_id', 'slug', 'image', 'title', 'description', 'param1', 'param2', 'param3', 'on_index', 'videos_num'])
            ->where(['enabled' => 1])
            ->orderBy($order)
            ->asArray();

        if (null !== $this->limit) {
            $query->limit($this->limit);
        }

        if ($this->isGroupByFirstLetter()) {
            $categories = [];

            foreach ($query->all() as $category) {
                $currentLetter = \mb_strtolower(\mb_substr($category['title'], 0, 1));

                if (\is_numeric($currentLetter)) {
                    $currentLetter = '#';
                }

                $categories[$currentLetter][] = $category;
            }

            return $categories;
        }

        return $query->all();
    }

    /**
     * Включен\выключен кеш виджета.
     *
     * @return boolean
     */
    private function isCacheEnabled(): bool
    {
        return (bool) $this->enableCache;
    }

    /**
     * Группировать или нет категории по первой букве.
     *
     * @return boolean
     */
    private function isGroupByFirstLetter(): bool
    {
        return (bool) $this->groupByFirstLetter;
    }

    /**
     * Создает ключ для кеша.
     *
     * @return string
     */
    private function buildCacheKey(): string
    {
        return "{$this->defaultCacheKey}:{$this->order}:{$this->template}:{$this->active_id}";
    }
}
