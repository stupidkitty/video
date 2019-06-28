<?php
namespace SK\VideoModule\Provider;

use Yii;
use yii\db\QueryInterface;
use yii\data\BaseDataProvider;
use SK\VideoModule\Model\Video;
use yii\db\ActiveQueryInterface;
use SK\VideoModule\Form\FilterForm;
use SK\VideoModule\Model\RotationStats;
use SK\VideoModule\Model\VideosCategoriesMap;

class RotateVideoProvider extends BaseDataProvider
{
    /**
     * @var QueryInterface the query that is used to fetch data models and [[totalCount]]
     * if it is not explicitly set.
     */
    public $query;
    /**
     * @var string|callable the column that is used as the key of the data models.
     * This can be either a column name, or a callable that returns the key value of a given data model.
     *
     * If this is not set, the following rules will be used to determine the keys of the data models:
     *
     * - If [[query]] is an [[\yii\db\ActiveQuery]] instance, the primary keys of [[\yii\db\ActiveQuery::modelClass]] will be used.
     * - Otherwise, the keys of the [[models]] array will be used.
     *
     * @see getKeys()
     */
    public $key;
    /**
     * Initializes the DB connection component.
     * This method will initialize the [[db]] property to make sure it refers to a valid DB connection.
     * @throws InvalidConfigException if [[db]] is invalid.
     */
    public $testPerPagePercent = 15;
    public $testVideosStartPosition = 4;
    public $category_id;

    public $filterForm;

    private $cache;

    /**
     *
     * SELECT `v`.*
     * FROM `videos` as `v`
     * LEFT JOIN `videos_stats` AS `vs` ON (`v`.`video_id` = `vs`.`video_id` AND `v`.`image_id` = `vs`.`image_id`)
     * WHERE `v`.`published_at` <= NOW() AND `v`.`status` = 10 AND `vs`.`category_id` = 9 AND `vs`.`best_image` = 1 AND `vs`.`tested_image` = 1
     * ORDER BY `vs`.`ctr` DESC
     */
    public function init()
    {
        parent::init();

        if (null === $this->filterForm) {
            $this->filterForm = new FilterForm;
        }

        $this->query = Video::find()
            ->alias('v')
            ->select('v.video_id, v.image_id, v.slug, v.title, v.orientation, v.video_preview, v.duration, v.likes, v.dislikes, v.comments_num, v.is_hd, v.views, v.template, v.published_at, vs.tested_image, vs.ctr')
            ->innerJoin(['vs' => RotationStats::tableName()], 'v.video_id = vs.video_id AND v.image_id = vs.image_id')
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1'])
                    ->where(['enabled' => 1]);
            }])
            ->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }]);

        if ('all-time' === $this->filterForm->t) {
            $this->query->untilNow();
        } else {
            $this->query->rangedUntilNow($this->filterForm->t);
        }

        $this->query
            ->onlyActive()
            ->andFilterWhere(['v.orientation' => $this->filterForm->orientation])
            ->andFilterWhere(['>=', 'v.duration', $this->filterForm->durationMin])
            ->andFilterWhere(['<=', 'v.duration', $this->filterForm->durationMax])
            ->andFilterWhere(['v.is_hd' => $this->filterForm->isHd])
            ->orderBy(['ctr' => SORT_DESC])
            ->asArray();

        $this->cache = Yii::$app->cache;
    }


    /**
     * @inheritdoc
     */
    protected function prepareModels()
    {
        $query = clone $this->query;
        $testPerPagePercent = (int) $this->testPerPagePercent;

        $totalCount = $this->getTotalCount();

        // если видосов в категории
        if (0 === $totalCount) {
            return [];
        }

        $pagination = $this->getPagination();

        if (false !== $pagination) {
            $pagination->totalCount = $totalCount;

            /** @var integer текущая страница */
            $page = $pagination->getPage();
            $perPage = $pagination->getPageSize();
            /** @var integer всего страниц */
            $totalPagesNum = $pagination->getPageCount();
        } else {
            $perPage = 20;
            $page = 0;
            $totalPagesNum = ceil($totalCount / $perPage);
        }

        /** @var integer сколько завершивших тест всего */
        $totalTestedCount = $this->getTestedCount();

        // если прошедших тест нет, выводим все по порядку.
        if (0 === $totalTestedCount) {
            return $query
               ->andWhere(['vs.category_id' => $this->category_id])
               ->andWhere(['vs.best_image' => 1])
               ->andWhere(['vs.tested_image' => 0])
               ->offset($pagination->getOffset())
               ->limit($pagination->getLimit())
               ->all();
        }

        /** @var integer сколько тестовых всего */
        $totalTestCount = $totalCount - $totalTestedCount;

        // если все прошли тест, выводим все по порядку.
        if (0 === $totalTestCount) {
            return $query
                ->andWhere(['vs.category_id' => $this->category_id])
                ->andWhere(['vs.best_image' => 1])
                ->andWhere(['vs.tested_image' => 1])
               ->offset($pagination->getOffset())
               ->limit($pagination->getLimit())
               ->all();
        }

       /** @var integer сколько тестовых на одну страницу по умолчанию */
        $testPerPage = ceil($perPage * $testPerPagePercent / 100);

        /** @var integer сколько завершивших тест на одну страницу по умолчанию */
        $testedPerPage = $perPage - $testPerPage;

        /** @var integer сколько страниц получилось завешивших тест */
        $testedPagesNum = ceil($totalTestedCount / $testedPerPage);

        /** @var integer сколько страниц получилось тестируемых тумб (нужна ли) */
        $testPagesNum = ceil($totalTestCount / $testPerPage);

        /** @var integer пограничная зона закончившихся тумб */
        $boundaryPage = (int) min($testedPagesNum, $testPagesNum);

        if (($page + 1) < $boundaryPage) { // считаем по процентам
            $offsetTested = $page * $testedPerPage;
            $limitTested = $testedPerPage;

            $offsetTest = $page * $testPerPage;
            $limitTest = $testPerPage;
        } elseif (($page + 1) === $boundaryPage) {
            if ($testedPagesNum == $boundaryPage) {

                $remainderTested = $totalTestedCount - ($page * $testedPerPage);
                $offsetTested = $totalTestedCount - $remainderTested;
                $limitTested = $remainderTested;

                $offsetTest = $page * $testPerPage;
                $limitTest = $perPage - $remainderTested;
            } else {
                $remainderTest = $totalTestCount - ($page * $testPerPage);
                $offsetTest = $totalTestCount - $remainderTest;
                $limitTest = $remainderTest;

                $offsetTested = $page * $testedPerPage;
                $limitTested = $perPage - $remainderTest;
            }
        } else {
            if ($testedPagesNum == $boundaryPage) {
                $remainderTested = $totalTestedCount - (($boundaryPage - 1) * $testedPerPage);
                $offsetTested = 0;
                $limitTested = 0;

                $offsetTest = ($boundaryPage - 1) * $testPerPage + ($perPage - $remainderTested);
                $offsetTest += ($page - $boundaryPage) * $perPage;
                $limitTest = $perPage;
            } else {
                $remainderTest = $totalTestCount - (($boundaryPage - 1) * $testPerPage);
                $offsetTest = 0;
                $limitTest = 0;

                $offsetTested = ($boundaryPage - 1) * $testedPerPage + ($perPage - $remainderTest);
                $offsetTested += ($page - $boundaryPage) * $perPage;
                $limitTested = $perPage;
            }
        }

        $testQuery = clone $query;

        $testedModels = $query
            ->andWhere(['vs.category_id' => $this->category_id])
            ->andWhere(['vs.best_image' => 1])
            ->andWhere(['vs.tested_image' => 1])
            ->offset((int) $offsetTested)
            ->limit((int) $limitTested)
            ->all();

        $testModels = $testQuery
            ->andWhere(['vs.category_id' => $this->category_id])
            ->andWhere(['vs.best_image' => 1])
            ->andWhere(['vs.tested_image' => 0])
            ->offset((int) $offsetTest)
            ->limit((int) $limitTest)
            ->all();

        // Перемешаем тестовые тумбы.
        $resultArray = $testedModels + $testModels;

        if (($page + 1) <= $boundaryPage && count($resultArray) > $this->testVideosStartPosition) {
            $firstVideos = array_splice($resultArray, 0, $this->testVideosStartPosition);
            shuffle($resultArray);

            return array_merge($firstVideos, $resultArray);
        }

        return $resultArray;
    }

    /**
     * @inheritdoc
     */
    protected function prepareKeys($models)
    {
        $keys = [];

        if ($this->key !== null) {
            foreach ($models as $model) {
                if (is_string($this->key)) {
                    $keys[] = $model[$this->key];
                } else {
                    $keys[] = call_user_func($this->key, $model);
                }
            }

            return $keys;
        } elseif ($this->query instanceof ActiveQueryInterface) {
            /* @var $class \yii\db\ActiveRecordInterface */
            $class = $this->query->modelClass;
            $pks = $class::primaryKey();

            if (count($pks) === 1) {
                $pk = $pks[0];

                foreach ($models as $model) {
                    $keys[] = $model[$pk];
                }
            } else {
                foreach ($models as $model) {
                    $kk = [];

                    foreach ($pks as $pk) {
                        $kk[$pk] = $model[$pk];
                    }

                    $keys[] = $kk;
                }
            }

            return $keys;
        }

        return array_keys($models);
    }

    /**
     * Подсчитывает количество активных видео в выбранной категории
     *
     * @return integer
     */
    protected function prepareTotalCount()
    {
        $query = Video::find()
            ->alias('v')
            ->innerJoin(['vcm' => VideosCategoriesMap::tableName()], 'v.video_id = vcm.video_id')
            ->andWhere(['vcm.category_id' => $this->category_id]);
        
        if ('all-time' === $this->filterForm->t) {
            $query->untilNow();
        } else {
            $query->rangedUntilNow($this->filterForm->t);
        }

        $count = $query    
            ->andWhere(['v.status' => Video::STATUS_ACTIVE])
            ->andFilterWhere(['v.orientation' => $this->filterForm->orientation])
            ->andFilterWhere(['>=', 'v.duration', $this->filterForm->durationMin])
            ->andFilterWhere(['<=', 'v.duration', $this->filterForm->durationMax])
            ->andFilterWhere(['v.is_hd' => $this->filterForm->isHd])
            ->cache(300)
            ->count();

        return (int) $count;
    }

    /**
     * Подсчитывает количество активных видео прошедших тестирование
     *
     * @return integer
     */
    protected function getTestedCount()
    {
        $query = Video::find()
            ->alias('v')
            ->innerJoin(['vs' => RotationStats::tableName()], 'v.video_id = vs.video_id AND v.image_id = vs.image_id')
            ->andWhere(['vs.category_id' => $this->category_id])
            ->andWhere(['vs.best_image' => 1])
            ->andWhere(['vs.tested_image' => 1]);

        if ('all-time' === $this->filterForm->t) {
            $query->untilNow();
        } else {
            $query->rangedUntilNow($this->filterForm->t);
        }

        $count = $query
            ->andWhere(['v.status' => Video::STATUS_ACTIVE])
            ->andFilterWhere(['v.orientation' => $this->filterForm->orientation])
            ->andFilterWhere(['>=', 'v.duration', $this->filterForm->durationMin])
            ->andFilterWhere(['<=', 'v.duration', $this->filterForm->durationMax])
            ->andFilterWhere(['v.is_hd' => $this->filterForm->isHd])
            ->cache(300)
            ->count();

        return (int) $count;
    }
}
