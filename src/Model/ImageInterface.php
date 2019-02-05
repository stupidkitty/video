<?php
namespace SK\VideoModule\Model;

interface ImageInterface
{
    /**
     * @return integer
     */
    public function getId();
    
    /**
     * @inheritdoc
     */
    public function setFile(\SplFileInfo $file);

    /**
     * @inheritdoc
     */
    public function getFile();
}
