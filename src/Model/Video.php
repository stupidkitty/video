<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;
use RS\Component\User\Model\User;
use SK\VideoModule\Query\VideoQuery;

/**
 * This is the model class for table "videos".
 *
 * @property integer $video_id
 * @property integer $image_id
 * @property integer $user_id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $short_description
 * @property integer $orientation
 * @property integer $duration
 * @property string $video_preview
 * @property string $video_url
 * @property string $embed
 * @property integer $on_index
 * @property integer $likes
 * @property integer $dislikes
 * @property integer $comments_num
 * @property boolean $is_hd
 * @property integer $views
 * @property string $template
 * @property integer $status
 * @property string $published_at
 * @property string $created_at
 * @property string $updated_at
 *
 * @property VideosCategories[] $videosCategories
 * @property Category[] $categories
 * @property Image[] $images
 */
class Video extends ActiveRecord implements VideoInterface, SlugAwareInterface
{
    use SlugGeneratorTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['slug', 'title', 'description', 'short_description', 'video_preview', 'video_url', 'source_url', 'embed', 'template'], 'string'],
            [['video_id', 'image_id', 'user_id', 'orientation', 'duration', 'likes', 'dislikes', 'comments_num', 'views', 'status'], 'integer'],
            [['is_hd', 'on_index', 'noindex', 'nofollow'], 'boolean'],
            [['published_at', 'created_at', 'updated_at'], 'datetime', 'format' => 'php: Y-m-d H:i:s'],
            [['is_hd', 'noindex', 'nofollow'], 'default', 'value' => 0],
            ['on_index', 'default', 'value' => 1],
            [['created_at', 'updated_at'], 'default', 'value' => \gmdate('Y-m-d H:i:s')],
            [['published_at'], 'default', 'value' => null],
        ];
    }

    public static function find()
    {
        return new VideoQuery(\get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->video_id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @inheritdoc
     */
    public function setTitle($title)
    {
        $this->title = (string) $title;
    }

    /**
     * @inheritdoc
     */
    public function getSlug()
    {
        return $this->slug;
    }

    /**
     * @inheritdoc
     */
    public function setSlug($slug)
    {
        $this->slug = (string) $slug;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getUser()
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    /**
     * @return boolean
     */
    public function hasPoster()
    {
        return null !== $this->poster;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getPoster()
    {
        return $this->hasOne(Image::class, ['image_id' => 'image_id']);
    }

    /**
     * @inheritdoc
     */
    public function setPoster(ImageInterface $image)
    {
        $this->link('poster', $image);
    }

    /**
     * @return boolean
     */
    public function hasImages()
    {
        return !empty($this->images);
    }

    /**
     * @return \yii\db\ActiveQuery[]
     */
    public function getImages()
    {
        return $this->hasMany(Image::class, ['video_id' => 'video_id']);
    }

    /**
     * @inheritdoc
     */
     public function addImage(ImageInterface $image)
     {
         $this->link('images', $image);
     }

    /**
     * @inheritdoc
     */
     public function removeImage(ImageInterface $image)
     {
         $this->unlink('images', $image);
     }

    /**
     * @return boolean
     */
    public function hasScreenshots()
    {
        return !empty($this->screenshots);
    }

    /**
     * @return \yii\db\ActiveQuery[]
     */
    public function getScreenshots()
    {
        return $this->hasMany(Screenshot::class, ['video_id' => 'video_id']);
    }

    /**
     * @inheritdoc
     */
     public function addScreenshot(Screenshot $screenshot)
     {
         $this->link('screenshots', $screenshot);
     }

    /**
     * @inheritdoc
     */
     public function removeScreenshot(Screenshot $screenshot)
     {
         $this->unlink('screenshots', $screenshot);
     }

    /**
     * @return boolean
     */
    public function hasCategories()
    {
        return !empty($this->categories);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategories()
    {
        return $this->hasMany(Category::class, ['category_id' => 'category_id'])
            ->viaTable(VideosCategories::tableName(), ['video_id' => 'video_id']);
    }

    /**
     * @inheritdoc
     */
     public function addCategory(CategoryInterface $category)
     {
         $exists = VideosCategories::find()
             ->where(['video_id' => $this->video_id, 'category_id' => $category->category_id])
             ->exists();

         if (!$exists) {
             return $this->link('categories', $category);
         }

         return true;
     }

    /**
     * @inheritdoc
     */
     public function removeCategory(CategoryInterface $category)
     {
         $this->unlink('categories', $category, true);
     }

     /**
      * Переводит длительность видео в формат часов: "24:52", или: "1:45:29"
      *
      * @return string
      */
     public function getDurationAsTime()
     {
         return \ltrim(\gmdate('H:i:s', $this->duration), '0:');
     }

    /**
     * Return list of status codes and labels
     *
     * @return array
     */
    public static function getStatuses()
    {
        return [
            self::STATUS_DISABLED  => 'Отключено',
            self::STATUS_ACTIVE    => 'Опубликовано',
            self::STATUS_MODERATE  => 'На модерации',
            self::STATUS_DELETED   => 'Удалено',
        ];
    }
}
