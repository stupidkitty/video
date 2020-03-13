<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_categories".
 *
 * @property integer $category_id
 * @property integer $position
 * @property string $slug
 * @property string $image
 * @property string $meta_title
 * @property string $meta_description
 * @property string $title
 * @property string $h1
 * @property string $description
 * @property string $seotext
 * @property string $param1
 * @property string $param2
 * @property string $param3
 * @property integer $videos_num
 * @property integer $on_index
 * @property boolean $enabled
 * @property integer $last_period_clicks
 * @property string $created_at
 * @property string $updated_at
 *
 * @property VideosCategories[] $videosCategories
 * @property Video[] $videos
 */
class Category extends ActiveRecord implements ToggleableInterface, SlugAwareInterface
{
    use SlugGeneratorTrait, ToggleableTrait;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_categories';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [
                [
                    'image',
                    'slug',
                    'title',
                    'meta_title',
                    'meta_description',
                    'description',
                    'h1',
                    'seotext',
                    'param1',
                    'param2',
                    'param3'
                ],
                'string'
            ],
            [['slug'], 'unique'],
            [
                [
                    'position',
                    'videos_num',
                    'last_period_clicks'
                ],
                'integer'
            ],
            [['on_index', 'enabled'], 'boolean'],
            [['created_at', 'updated_at'], 'datetime', 'format' => 'php:Y-m-d H:i:s'],
            [['created_at', 'updated_at'], 'default', 'value' => \gmdate('Y-m-d H:i:s')],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideos()
    {
        return $this
            ->hasMany(Video::class, ['video_id' => 'video_id'])
            ->viaTable(VideosCategories::tableName(), ['category_id' => 'category_id']);
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->category_id;
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
}
