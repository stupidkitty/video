<?php

namespace SK\VideoModule\Statistic\Report;

class VideoStatisticReport
{
    private int $totalVideos = 0;
    private int $disabledVideos = 0;
    private int $activeVideos = 0;
    private int $moderateVideos = 0;
    private int $deletedVideos = 0;
    private int $totalCategories = 0;
    private int $enabledCategories = 0;
    private int $disabledCategories = 0;
    private int $totalImages = 0;
    private ?\DateTimeInterface $lastPublicationDate;

    public function getTotalVideos(): int
    {
        return $this->totalVideos;
    }

    public function setTotalVideos(int $totalVideos): static
    {
        $this->totalVideos = $totalVideos;

        return $this;
    }

    public function getDisabledVideos(): int
    {
        return $this->disabledVideos;
    }

    public function setDisabledVideos(int $disabledVideos): static
    {
        $this->disabledVideos = $disabledVideos;

        return $this;
    }

    public function getActiveVideos(): int
    {
        return $this->activeVideos;
    }

    public function setActiveVideos(int $activeVideos): static
    {
        $this->activeVideos = $activeVideos;

        return $this;
    }

    public function getModerateVideos(): int
    {
        return $this->moderateVideos;
    }

    public function setModerateVideos(int $moderateVideos): static
    {
        $this->moderateVideos = $moderateVideos;

        return $this;
    }

    public function getDeletedVideos(): int
    {
        return $this->deletedVideos;
    }

    public function setDeletedVideos(int $deletedVideos): static
    {
        $this->deletedVideos = $deletedVideos;

        return $this;
    }

    public function getTotalCategories(): int
    {
        return $this->totalCategories;
    }

    public function setTotalCategories(int $totalCategories): static
    {
        $this->totalCategories = $totalCategories;

        return $this;
    }

    public function getEnabledCategories(): int
    {
        return $this->enabledCategories;
    }

    public function setEnabledCategories(int $enabledCategories): static
    {
        $this->enabledCategories = $enabledCategories;

        return $this;
    }

    public function getDisabledCategories(): int
    {
        return $this->disabledCategories;
    }

    public function setDisabledCategories(int $disabledCategories): static
    {
        $this->disabledCategories = $disabledCategories;

        return $this;
    }

    public function getTotalImages(): int
    {
        return $this->totalImages;
    }

    public function setTotalImages(int $totalImages): static
    {
        $this->totalImages = $totalImages;

        return $this;
    }

    public function getLastPublicationDate(): ?\DateTimeInterface
    {
        return $this->lastPublicationDate;
    }

    public function setLastPublicationDate(?\DateTimeInterface $lastPublicationDate): static
    {
        $this->lastPublicationDate = $lastPublicationDate;

        return $this;
    }
}
