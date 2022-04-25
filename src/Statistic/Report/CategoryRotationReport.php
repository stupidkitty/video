<?php

namespace SK\VideoModule\Statistic\Report;

class CategoryRotationReport
{
    private int $id;
    private string $title = '';
    private string $slug = '';
    private int $totalThumbs = 0;
    private int $testThumbs = 0;
    private int $testedThumbs = 0;

    public function getTotalTestPercent(): float
    {
        return $this->computePercent($this->testedThumbs, $this->totalThumbs);
    }

    private function computePercent($a, $b): float
    {
        return ($b > 0) ? round(($a / $b * 100), 2) : 0;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getTitle(): string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getSlug(): string
    {
        return $this->slug;
    }

    public function setSlug($slug): static
    {
        $this->slug = (string) $slug;

        return $this;
    }

    public function getTotalThumbs(): int
    {
        return $this->totalThumbs;
    }

    public function setTotalThumbs(int $totalThumbs): static
    {
        $this->totalThumbs = $totalThumbs;

        return $this;
    }

    public function getTestedThumbs(): int
    {
        return $this->testedThumbs;
    }

    public function setTestedThumbs(int $testedThumbs): static
    {
        $this->testedThumbs = $testedThumbs;

        return $this;
    }

    public function getTestThumbs(): int
    {
        return $this->testThumbs;
    }

    public function setTestThumbs(int $testThumbs): static
    {
        $this->testThumbs = $testThumbs;

        return $this;
    }
}
