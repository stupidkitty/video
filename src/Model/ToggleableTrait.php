<?php
namespace SK\VideoModule\Model;

trait ToggleableTrait
{
    /**
     * @var bool
     */
    //protected $enabled = true;
    /**
     * @return bool
     */
    public function isEnabled()
    {
        return (bool) $this->enabled;
    }
    /**
     * @param bool $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = (bool) $enabled;
    }
    public function enable()
    {
        $this->enabled = true;
    }
    public function disable()
    {
        $this->enabled = false;
    }
}