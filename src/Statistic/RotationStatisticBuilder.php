<?php
namespace SK\VideoModule\Statistic;

use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosCategories;
use SK\VideoModule\Statistic\Report\CategoryRotationReport;
use SK\VideoModule\Statistic\Report\RotationStatisticReport;
use yii\db\Expression;

class RotationStatisticBuilder
{

    public function build()
    {
        $report = new RotationStatisticReport();

        $report->setTotalThumbs($this->calculateTotalItems());
        $report->setTestThumbs($this->calculateTestItems());
        $report->setTestedThumbs($this->calculateTestedItems());
        $report->setTestedZeroCtrThumbs($this->calculateTestedZeroCtrItems());

        $report->setCategoriesReports($this->buildCategoriesReport());

        return $report;
    }

    public function buildCategoriesReport()
    {
        $categoriesReports = [];

        $categoriesTotalThumbs = $this->calculateCategoriesTotalThumbs();
        $categoriesTestThumbs = $this->calculateCategoriesTestThumbs();
        $categoriesTestedThumbs = $this->calculateCategoriesTestedThumbs();
        $categoriesAutopostingThumbs = $this->calculateCategoriesAutopostingThumbs();

        $categories = Category::find()
            ->select(['category_id', 'title', 'slug'])
            ->where(['enabled' => 1])
            ->indexBy('category_id')
            ->all();

        foreach ($categories as $key => $category) {
            $report = new CategoryRotationReport();

            $report->setId($category->getId());
            $report->setTitle($category->getTitle());
            $report->setSlug($category->getSlug());

            $categoryTotalThumbs = isset($categoriesTotalThumbs[$key]) ? (int) $categoriesTotalThumbs[$key] : 0;
            $report->setTotalThumbs($categoryTotalThumbs);

            $categoryTestThumbs = isset($categoriesTestThumbs[$key]) ? (int) $categoriesTestThumbs[$key] : 0;
            $report->setTestThumbs($categoryTestThumbs);

            $categoryTestedThumbs = isset($categoriesTestedThumbs[$key]) ? (int) $categoriesTestedThumbs[$key] : 0;
            $report->setTestedThumbs($categoryTestedThumbs);

            if (isset($categoriesAutopostingThumbs[$key])) {
                $report->setAutopostingThumbs((int) $categoriesAutopostingThumbs[$key]);
                $report->setUntilNowTotalThumbs($categoryTotalThumbs - (int) $categoriesAutopostingThumbs[$key]);
            } else {
                $report->setUntilNowTotalThumbs($categoryTotalThumbs);
            }

            $categoriesReports[] = $report;
        }

        return $categoriesReports;
    }

    /**
     * Подсчитывает все активные видео в ротации.
     *
     * @return integer
     */
    protected function calculateTotalItems()
    {
        $num = VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает протестированные активные видео в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTestItems()
    {
        $num = VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 0, '{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает нетестированные активные тумбы в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTestedItems()
    {
        $num = VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 1, '{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает нетестированные активные тумбы в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTestedZeroCtrItems()
    {
        $num = VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 1, '{{vs}}.{{ctr}}' => 0, '{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает все активные тумбы в таблице ротации группируя по категориям.
     *
     * @return integer
     */
    protected function calculateCategoriesTotalThumbs()
    {
        $totalThumbs = VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();

        return $totalThumbs;
    }

    /**
     * Подсчитывает все активные тумбы в таблице ротации группируя по категориям.
     *
     * @return integer
     */
    protected function calculateCategoriesUntilNowThumbs()
    {
        $totalThumbs = VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            //->where(['<=', '{{v}}.{{published_at}}', new Expression('NOW()')])
            ->andWhere(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();

        return $totalThumbs;
    }

    /**
     * Подсчитывает тумбы в автопостинге группируя по категориям.
     *
     * @return integer
     */
    protected function calculateCategoriesAutopostingThumbs()
    {
        $totalThumbs = VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            //->where(['>=', '{{v}}.{{published_at}}', new Expression('NOW()')])
            ->andWhere(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();

        return $totalThumbs;
    }

    /**
     * Подсчитывает нетестированные тумбы в таблице ротации группируя по категориям.
     *
     * @return integer
     */
    protected function calculateCategoriesTestThumbs()
    {
        $testedThumbs = VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 0])
            //->andWhere(['<=', '{{v}}.{{published_at}}', new Expression('NOW()')])
            ->andWhere(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();

        return $testedThumbs;
    }

    /**
     * Подсчитывает протестированные тумбы в таблице ротации группируя по категориям.
     *
     * @return integer
     */
    protected function calculateCategoriesTestedThumbs()
    {
        $testedThumbs = VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 1])
            //->andWhere(['<=', '{{v}}.{{published_at}}', new Expression('NOW()')])
            ->andWhere(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();

        return $testedThumbs;
    }
}
