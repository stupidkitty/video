<?php

namespace SK\VideoModule\Statistic;

use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideoInterface;
use SK\VideoModule\Model\VideosCategories;
use SK\VideoModule\Statistic\Report\CategoryRotationReport;
use SK\VideoModule\Statistic\Report\RotationStatisticReport;
use yii\db\Expression;

class RotationStatisticBuilder
{
    public function build(): RotationStatisticReport
    {
        $report = new RotationStatisticReport();

        $report->setTotalThumbs($this->calculateTotalItems());
        $report->setTestThumbs($this->calculateTestItems());
        $report->setTestedThumbs($this->calculateTestedItems());
        $report->setTestedZeroCtrThumbs($this->calculateTestedZeroCtrItems());

        $report->setCategoriesReports($this->buildCategoriesReport());

        return $report;
    }

    public function buildCategoriesReport(): array
    {
        $categoriesReports = [];

        $categoriesTotalThumbs = $this->calculateCategoriesTotalThumbs();
        $categoriesTestThumbs = $this->calculateCategoriesTestThumbs();
        $categoriesTestedThumbs = $this->calculateCategoriesTestedThumbs();

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

            $categoriesReports[] = $report;
        }

        return $categoriesReports;
    }

    /**
     * Подсчитывает все активные видео в ротации.
     *
     * @return int
     */
    private function calculateTotalItems(): int
    {
        return VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Подсчитывает протестированные активные видео в таблице ротации.
     *
     * @return int
     */
    private function calculateTestItems(): int
    {
        return VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 0, '{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Подсчитывает нетестированные активные тумбы в таблице ротации.
     *
     * @return int
     */
    private function calculateTestedItems(): int
    {
        return VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 1, '{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Подсчитывает нетестированные активные тумбы в таблице ротации.
     *
     * @return int
     */
    private function calculateTestedZeroCtrItems(): int
    {
        return VideosCategories::find()
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 1, '{{vs}}.{{ctr}}' => 0, '{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Подсчитывает все активные тумбы в таблице ротации группируя по категориям.
     *
     * @return array
     */
    private function calculateCategoriesTotalThumbs(): array
    {
        return VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();
    }

    /**
     * Подсчитывает все активные тумбы в таблице ротации группируя по категориям.
     *
     * @return array
     */
    private function calculateCategoriesUntilNowThumbs(): array
    {
        return VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->andWhere(['{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();
    }

    /**
     * Подсчитывает нетестированные тумбы в таблице ротации группируя по категориям.
     *
     * @return array
     */
    private function calculateCategoriesTestThumbs(): array
    {
        return VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 0])
            ->andWhere(['{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();
    }

    /**
     * Подсчитывает протестированные тумбы в таблице ротации группируя по категориям.
     *
     * @return array
     */
    private function calculateCategoriesTestedThumbs(): array
    {
        return VideosCategories::find()
            ->select(new Expression('COUNT(*) as cnt'))
            ->alias('vs')
            ->innerJoin(['v' => 'videos'], '{{vs}}.{{video_id}}={{v}}.{{video_id}}')
            ->where(['{{vs}}.{{is_tested}}' => 1])
            ->andWhere(['{{v}}.{{status}}' => VideoInterface::STATUS_ACTIVE])
            ->groupBy('{{vs}}.{{category_id}}')
            ->indexBy('category_id')
            ->column();
    }
}
