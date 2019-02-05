<?php
namespace SK\VideoModule\Statistic\Report;

class CategoryRotationReport
{
    private $id;
    private $title = '';
    private $slug = '';
    private $totalThumbs = 0;
    private $testThumbs = 0;
    private $testedThumbs = 0;
    private $untilNowTotalThumbs = 0;
    private $autopostingThumbs = 0;

    public function getTotalTestPercent()
    {
        return $this->computePercent($this->testedThumbs, $this->totalThumbs);
    }

    public function getUntilNowPercent()
    {
        return $this->computePercent($this->untilNowTotalThumbs, $this->totalThumbs);
    }

    public function getUntilNowTestedPercent()
    {
        return $this->computePercent($this->testedThumbs, $this->untilNowTotalThumbs);
    }

    public function getAutopostingPercent()
    {
        return $this->computePercent($this->autopostingThumbs, $this->totalThumbs);
    }

    private function computePercent($a, $b)
    {
        return ($b > 0) ? round(($a / $b * 100), 2) : 0;
    }

    public function getId()
    {
        return $this->id;
    }

    public function setId($id)
    {
        $this->id = (int) $id;
    }

    public function getTitle()
    {
        return $this->title;
    }

    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    public function getSlug()
    {
        return $this->slug;
    }

    public function setSlug($slug)
    {
        $this->slug = (string) $slug;
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

    public function getUntilNowTotalThumbs()
    {
        return $this->untilNowTotalThumbs;
    }

    public function setUntilNowTotalThumbs($untilNowTotalThumbs)
    {
        $this->untilNowTotalThumbs = (int) $untilNowTotalThumbs;
    }

    public function getAutopostingThumbs()
    {
        return $this->autopostingThumbs;
    }

    public function setAutopostingThumbs($autopostingThumbs)
    {
        $this->autopostingThumbs = (int) $autopostingThumbs;
    }
}
