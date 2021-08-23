<?php

namespace SK\VideoModule\Model;

use RS\Component\User\Model\User;
use SK\VideoModule\Query\VideoQuery;
use yii\base\InvalidConfigException;
use yii\db\ActiveQuery;
use yii\db\ActiveRecord;
use yii\db\Exception;
use yii\db\StaleObjectException;

/**
 * This is the model class for table "videos".
 *
 * @property integer $video_id
 * @property integer $image_id
 * @property integer $user_id
 * @property string $slug
 * @property string $title
 * @property string $description
 * @property string $search_field
 * @property integer $orientation
 * @property integer $duration
 * @property string $video_preview
 * @property string $embed
 * @property integer $on_index
 * @property integer $likes
 * @property integer $dislikes
 * @property integer $comments_num
 * @property boolean $is_hd
 * @property integer $views
 * @property string $template
 * @property integer $status
 * @property float $max_ctr
 * @property string $published_at
 * @property string $created_at
 * @property string $updated_at
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
    public static function tableName(): string
    {
        return '{{%videos}}';
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['slug', 'title', 'description', 'search_field', 'video_preview', 'embed', 'template', 'custom1', 'custom2', 'custom3'], 'string'],
            [['video_id', 'image_id', 'user_id', 'orientation', 'duration', 'likes', 'dislikes', 'comments_num', 'views', 'status'], 'integer'],
            [['is_hd', 'on_index', 'noindex', 'nofollow'], 'boolean'],
            [['max_ctr'], 'number'],
            [['published_at', 'created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['is_hd', 'noindex', 'nofollow'], 'default', 'value' => 0],
            ['on_index', 'default', 'value' => 1],
            [['created_at', 'updated_at'], 'default', 'value' => \gmdate('Y-m-d H:i:s')],
            [['published_at'], 'default', 'value' => null],
        ];
    }

    public static function find(): VideoQuery
    {
        return new VideoQuery(\get_called_class());
    }

    /**
     * @inheritdoc
     */
    public function getId(): ?int
    {
        return $this->video_id;
    }

    /**
     * @inheritdoc
     */
    public function getTitle(): string
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
    public function getSlug(): ?string
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
     * @return ActiveQuery
     */
    public function getUser(): ActiveQuery
    {
        return $this->hasOne(User::class, ['user_id' => 'user_id']);
    }

    /**
     * @return boolean
     */
    public function hasPoster(): bool
    {
        return null !== $this->poster;
    }

    /**
     * @return ActiveQuery
     */
    public function getPoster(): ActiveQuery
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
    public function hasImages(): bool
    {
        return !empty($this->images);
    }

    /**
     * @return ActiveQuery
     */
    public function getImages(): ActiveQuery
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
     * @throws StaleObjectException
     * @throws Exception
     */
    public function removeImage(ImageInterface $image)
    {
        $this->unlink('images', $image);
    }

    /**
     * @return boolean
     */
    public function hasScreenshots(): bool
    {
        return !empty($this->screenshots);
    }

    /**
     * @return ActiveQuery
     */
    public function getScreenshots(): ActiveQuery
    {
        return $this->hasMany(Screenshot::class, ['video_id' => 'video_id']);
    }

    public function addScreenshot(Screenshot $screenshot)
    {
        $this->link('screenshots', $screenshot);
    }

    /**
     * @throws StaleObjectException
     * @throws Exception
     */
    public function removeScreenshot(Screenshot $screenshot)
    {
        $this->unlink('screenshots', $screenshot);
    }

    /**
     * @return boolean
     */
    public function hasCategories(): bool
    {
        return !empty($this->categories);
    }

    /**
     * @return ActiveQuery
     * @throws InvalidConfigException
     */
    public function getCategories(): ActiveQuery
    {
        return $this->hasMany(Category::class, ['category_id' => 'category_id'])
            ->viaTable(VideosCategories::tableName(), ['video_id' => 'video_id']);
    }

    /**
     * @inheritdoc
     */
    public function addCategory(Category $category): bool
    {
        $exists = VideosCategories::find()
            ->where(['video_id' => $this->video_id, 'category_id' => $category->category_id])
            ->exists();

        if (!$exists) {
            $this->link('categories', $category);
        }

        return true;
    }

    /**
     * @throws StaleObjectException
     * @throws Exception
     */
    public function removeCategory(Category $category)
    {
        $this->unlink('categories', $category, true);
    }

    /**
     * Переводит длительность видео в формат часов: "24:52", или: "1:45:29"
     *
     * @return string
     */
    public function getDurationAsTime(): string
    {
        return \ltrim(\gmdate('H:i:s', $this->duration), '0:');
    }

    /**
     * Return list of status codes and labels
     *
     * @return array
     */
    public static function getStatuses(): array
    {
        return [
            self::STATUS_DISABLED => 'Отключено',
            self::STATUS_ACTIVE => 'Опубликовано',
            self::STATUS_MODERATE => 'На модерации',
            self::STATUS_DELETED => 'Удалено',
        ];
    }
}
