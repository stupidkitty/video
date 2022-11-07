<?php

namespace SK\VideoModule\Statistic;

use DateTime;
use DateTimeInterface;
use SK\VideoModule\Model\VideoInterface;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Image;
use SK\VideoModule\Statistic\Report\VideoStatisticReport;

class VideoStatisticBuilder
{
    public function build(): VideoStatisticReport
    {
        $report = new VideoStatisticReport();

        $report->setTotalVideos($this->countTotalVideos());
        $report->setDisabledVideos($this->countDisabledVideos());
        $report->setActiveVideos($this->countActiveVideos());
        $report->setModerateVideos($this->countModerateVideos());
        $report->setDeletedVideos($this->countDeletedVideos());
        $report->setLastPublicationDate($this->getLastPublicationDate());

        $report->setTotalCategories($this->countTotalCategories());
        $report->setEnabledCategories($this->countEnabledCategories());
        $report->setDisabledCategories($this->countDisabledCategories());
        $report->setTotalImages($this->countTotalImages());

        return $report;
    }

    /**
     * Подсчитывает все видео в базе.
     *
     * @return int
     */
    private function countTotalVideos(): int
    {
        return Video::find()
            ->count();
    }

    /**
     * Подсчитывает видео со статусом "disabled".
     *
     * @return int
     */
    private function countDisabledVideos(): int
    {
        return Video::find()
            ->where(['status' => VideoInterface::STATUS_DISABLED])
            ->count();
    }

    /**
     * Подсчитывает видео со статусом "active".
     *
     * @return int
     */
    private function countActiveVideos(): int
    {
        return Video::find()
            ->where(['status' => VideoInterface::STATUS_ACTIVE])
            ->count();
    }

    /**
     * Подсчитывает видео со статусом "moderation".
     *
     * @return int
     */
    private function countModerateVideos(): int
    {
        return Video::find()
            ->where(['status' => VideoInterface::STATUS_MODERATE])
            ->count();
    }

    /**
     * Подсчитывает видео со статусом "delete".
     *
     * @return int
     */
    private function countDeletedVideos(): int
    {
        return Video::find()
            ->where(['status' => VideoInterface::STATUS_DELETED])
            ->count();
    }

    /**
     * Подсчитывает все категории.
     *
     * @return int
     */
    private function countTotalCategories(): int
    {
        return Category::find()
            ->count();
    }

    /**
     * Подсчитывает активные категории.
     *
     * @return int
     */
    private function countEnabledCategories(): int
    {
        return Category::find()
            ->where(['enabled' => 1])
            ->count();
    }

    /**
     * Подсчитывает отключенные категории.
     *
     * @return integer
     */
    private function countDisabledCategories(): int
    {
        return Category::find()
            ->where(['enabled' => 0])
            ->count();
    }

    /**
     * Подсчитывает все изображения.
     *
     * @return int
     */
    private function countTotalImages(): int
    {
        return Image::find()
            ->count();
    }

    /**
     * @throws \Exception
     */
    private function getLastPublicationDate(): ?DateTimeInterface
    {
        $date = Video::find()
            ->where(['status' => VideoInterface::STATUS_ACTIVE])
            ->max('published_at');

        return $date ? new DateTime($date) : null;
    }
}
