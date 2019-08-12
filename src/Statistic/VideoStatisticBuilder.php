<?php
namespace SK\VideoModule\Statistic;

use yii\db\Expression;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Statistic\Report\VideoStatisticReport;

class VideoStatisticBuilder
{

    public function build()
    {
        $report = new VideoStatisticReport();

        $report->setTotalVideos($this->countTotalVideos());
        $report->setDisabledVideos($this->countDisabledVideos());
        $report->setActiveVideos($this->countActiveVideos());
        $report->setModerateVideos($this->countModerateVideos());
        $report->setDeletedVideos($this->countDeletedVideos());
        $report->setAutopostingVideos($this->countAutopostingVideos());

        $report->setTotalCategories($this->countTotalCategories());
        $report->setEnabledCategories($this->countEnabledCategories());
        $report->setDisabledCategories($this->countDisabledCategories());
        $report->setTotalImages($this->countTotalImages());

        return $report;
    }

    /**
     * Подсчитывает все видео в базе.
     *
     * @return integer
     */
    protected function countTotalVideos()
    {
        $num = Video::find()
            ->count();

        return $num;
    }

    /**
     * Подсчитывает видео со статусом "disabled".
     *
     * @return integer
     */
    protected function countDisabledVideos()
    {
        $num = Video::find()
            ->where(['status' => Video::STATUS_DISABLED])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает видео со статусом "active".
     *
     * @return integer
     */
    protected function countActiveVideos()
    {
        $num = Video::find()
            ->where(['status' => Video::STATUS_ACTIVE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает видео со статусом "moderation".
     *
     * @return integer
     */
    protected function countModerateVideos()
    {
        $num = Video::find()
            ->where(['status' => Video::STATUS_MODERATE])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает видео со статусом "delete".
     *
     * @return integer
     */
    protected function countDeletedVideos()
    {
        $num = Video::find()
            ->where(['status' => Video::STATUS_DELETED])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает сколько видео находится в автопостинге.
     *
     * @return integer
     */
    protected function countAutopostingVideos()
    {
        $num = Video::find()
            ->alias('v')
            ->where(['>=', 'v.published_at', new Expression('NOW()')])
            ->onlyActive()
            ->count();

        return $num;
    }

    /**
     * Подсчитывает все категории.
     *
     * @return integer
     */
    protected function countTotalCategories()
    {
        $num = Category::find()
            ->count();

        return $num;
    }

    /**
     * Подсчитывает активные категории.
     *
     * @return integer
     */
    protected function countEnabledCategories()
    {
        $num = Category::find()
            ->where(['enabled' => 1])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает отключенные категории.
     *
     * @return integer
     */
    protected function countDisabledCategories()
    {
        $num = Category::find()
            ->where(['enabled' => 0])
            ->count();

        return $num;
    }

    /**
     * Подсчитывает все изображения.
     *
     * @return integer
     */
    protected function countTotalImages()
    {
        $num = Image::find()
            ->count();

        return $num;
    }
}
