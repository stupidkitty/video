<?php
namespace  SK\VideoModule\Model;

interface VideoInterface
{
    const STATUS_DISABLED = 0;
    const STATUS_ACTIVE = 10;
    const STATUS_MODERATE = 20;
    const STATUS_DELETED = 90;

    /**
     * @return integer
     */
    public function getId();

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $title
     */
    public function setTitle($title);

    /**
     * @return bool
     */
    public function hasPoster();

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoster();

    public function setPoster(ImageInterface $image);

    /**
     * @return bool
     */
    public function hasImages();

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImages();

    /**
     * @param ImageInterface $image
     */
    public function addImage(ImageInterface $image);

    /**
     * @param ImageInterface $image
     */
    public function removeImage(ImageInterface $image);

    /**
     * @return bool
     */
    public function hasCategories();

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories();

    public function addCategory(Category $category);

    public function removeCategory(Category $category);

    public static function getStatuses();
}
