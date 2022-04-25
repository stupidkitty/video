<?php

namespace SK\VideoModule\Statistic\Report;

class RotationStatisticReport
{
    private int $totalThumbs = 0;
    private int $testThumbs = 0;
    private int $testedThumbs = 0;
    private int $testedZeroCtrThumbs = 0;

    /**
     * @var CategoryRotationReport[]
     */
    private array $categoriesReports = [];

    private function computeTotalTestPercent(): float
    {
        return ($this->totalThumbs > 0) ? round(($this->testedThumbs / $this->totalThumbs * 100), 2) : 0;
    }

    public function getTotalTestPercent(): float
    {
        return $this->computeTotalTestPercent();
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

    public function getTestedZeroCtrThumbs(): int
    {
        return $this->testedZeroCtrThumbs;
    }

    public function setTestedZeroCtrThumbs($testedZeroCtrThumbs): static
    {
        $this->testedZeroCtrThumbs = $testedZeroCtrThumbs;

        return $this;
    }

    public function hasCategoriesReports(): bool
    {
        return \count($this->categoriesReports) !== 0;
    }

    public function getCategoriesReports(): array
    {
        return $this->categoriesReports;
    }

    /**
     * @param CategoryRotationReport[] $categoriesReports
     * @return RotationStatisticReport
     */
    public function setCategoriesReports(array $categoriesReports): static
    {
        $this->categoriesReports = $categoriesReports;

        return $this;
    }
}
