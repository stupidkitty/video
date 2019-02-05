<?php
namespace SK\VideoModule\Statistic\Report;

class VideoStatisticReport
{
    private $totalVideos = 0;
    private $disabledVideos = 0;
    private $activeVideos = 0;
    private $moderateVideos = 0;
    private $deletedVideos = 0;
    private $autopostingVideos = 0;

    private $totalCategories = 0;
    private $enabledCategories = 0;
    private $disabledCategories = 0;

    private $totalImages = 0;

    public function getTotalVideos()
    {
        return $this->totalVideos;
    }

    public function setTotalVideos($totalVideos)
    {
        $this->totalVideos = (int) $totalVideos;
    }

    public function getDisabledVideos()
    {
        return $this->disabledVideos;
    }

    public function setDisabledVideos($disabledVideos)
    {
        $this->disabledVideos = (int) $disabledVideos;
    }

    public function getActiveVideos()
    {
        return $this->activeVideos;
    }

    public function setActiveVideos($activeVideos)
    {
        $this->activeVideos = (int) $activeVideos;
    }

    public function getModerateVideos()
    {
        return $this->moderateVideos;
    }

    public function setModerateVideos($moderateVideos)
    {
        $this->moderateVideos = (int) $moderateVideos;
    }

    public function getDeletedVideos()
    {
        return $this->deletedVideos;
    }

    public function setDeletedVideos($deletedVideos)
    {
        $this->deletedVideos = (int) $deletedVideos;
    }

    public function getAutopostingVideos()
    {
        return $this->autopostingVideos;
    }

    public function setAutopostingVideos($autopostingVideos)
    {
        $this->autopostingVideos = (int) $autopostingVideos;
    }

    public function getTotalCategories()
    {
        return $this->totalCategories;
    }

    public function setTotalCategories($totalCategories)
    {
        $this->totalCategories = (int) $totalCategories;
    }

    public function getEnabledCategories()
    {
        return $this->enabledCategories;
    }

    public function setEnabledCategories($enabledCategories)
    {
        $this->enabledCategories = (int) $enabledCategories;
    }

    public function getDisabledCategories()
    {
        return $this->disabledCategories;
    }

    public function setDisabledCategories($disabledCategories)
    {
        $this->disabledCategories = (int) $disabledCategories;
    }

    public function getTotalImages()
    {
        return $this->totalImages;
    }

    public function setTotalImages($totalImages)
    {
        $this->totalImages = (int) $totalImages;
    }
}
