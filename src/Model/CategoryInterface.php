<?php
namespace SK\VideoModule\Model;

interface CategoryInterface
{
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideos();

    /**
     * @return integer
     */
    public function getId();
}
