<?php
namespace SK\VideoModule\Statistic\Report;

class RotationStatisticReport
{
    private $totalThumbs = 0;
    private $testThumbs = 0;
    private $testedThumbs = 0;
    private $testedZeroCtrThumbs = 0;
    private $categoriesReports = [];

    private function computeTotalTestPercent()
    {
        return ($this->totalThumbs > 0) ? round(($this->testedThumbs / $this->totalThumbs * 100), 2) : 0;
    }

    public function getTotalTestPercent()
    {
        return $this->computeTotalTestPercent();
    }

    public function getTotalThumbs()
    {
        return $this->totalThumbs;
    }

    public function setTotalThumbs($totalThumbs)
    {
        $this->totalThumbs = (int) $totalThumbs;
    }

    public function getTestedThumbs()
    {
        return $this->testedThumbs;
    }

    public function setTestedThumbs($testedThumbs)
    {
        $this->testedThumbs = (int) $testedThumbs;
    }

    public function getTestThumbs()
    {
        return $this->testThumbs;
    }

    public function setTestThumbs($testThumbs)
    {
        $this->testThumbs = (int) $testThumbs;
    }

    public function getTestedZeroCtrThumbs()
    {
        return $this->testedZeroCtrThumbs;
    }

    public function setTestedZeroCtrThumbs($testedZeroCtrThumbs)
    {
        $this->testedZeroCtrThumbs = (int) $testedZeroCtrThumbs;
    }

    public function hasCategoriesReports()
    {
        return !empty($this->categoriesReports);
    }

    public function getCategoriesReports()
    {
        return $this->categoriesReports;
    }

    public function setCategoriesReports($categoriesReports)
    {
        $this->categoriesReports = (array) $categoriesReports;
    }
}
