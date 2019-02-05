<?php
namespace SK\VideoModule\Statistic;

use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\RotationStats;
use SK\VideoModule\Statistic\Report\CategoryRotationReport;
use SK\VideoModule\Statistic\Report\RotationStatisticReport;

class RotationStatisticBuilder
{

    public function build()
    {
        $report = new RotationStatisticReport();

        $report->setTotalThumbs($this->calculateTotalThumbs());
        $report->setTestThumbs($this->calculateTestThumbs());
        $report->setTestedThumbs($this->calculateTestedThumbs());
        $report->setTestedZeroCtrThumbs($this->calculateTestedZeroCtrThumbs());

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
                $report->setUntilNowTotalThumbs($categoryTotalThumbs - $categoriesAutopostingThumbs[$key]);
            } else {
                $report->setUntilNowTotalThumbs($categoryTotalThumbs);
            }

            $categoriesReports[] = $report;
        }

        return $categoriesReports;
    }

    /**
     * Подсчитывает все активные тумбы в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTotalThumbs()
    {
        $num = RotationStats::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает протестированные активные тумбы в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTestThumbs()
    {
        $num = RotationStats::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{tested_image}}' => 0, '{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает нетестированные активные тумбы в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTestedThumbs()
    {
        $num = RotationStats::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{tested_image}}' => 1, '{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает нетестированные активные тумбы в таблице ротации.
     *
     * @return integer
     */
    protected function calculateTestedZeroCtrThumbs()
    {
        $num = RotationStats::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{tested_image}}' => 1, '{{vs}}.{{ctr}}' => 0,  '{{v}}.{{status}}' => Video::STATUS_ACTIVE])
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
        $totalThumbs = RotationStats::find()
            ->select(new \yii\db\Expression('COUNT(*) as cnt'))
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
        $totalThumbs = RotationStats::find()
            ->select(new \yii\db\Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->andWhere(['<=', '{{v}}.{{published_at}}', new \yii\db\Expression('NOW()')])
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
        $totalThumbs = RotationStats::find()
            ->select(new \yii\db\Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->andWhere(['>=', '{{v}}.{{published_at}}', new \yii\db\Expression('NOW()')])
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
        $testedThumbs = RotationStats::find()
            ->select(new \yii\db\Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->andWhere(['{{vs}}.{{tested_image}}' => 0])
            ->andWhere(['<=', '{{v}}.{{published_at}}', new \yii\db\Expression('NOW()')])
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
        $testedThumbs = RotationStats::find()
            ->select(new \yii\db\Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->andWhere(['{{vs}}.{{tested_image}}' => 1])
            ->andWhere(['<=', '{{v}}.{{published_at}}', new \yii\db\Expression('NOW()')])
            ->andWhere(['{{v}}.{{status}}' => Video::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();

        return $testedThumbs;
    }
}
