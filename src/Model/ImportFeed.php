<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_import_feeds".
 *
 * @property string $name
 * @property string $delimiter
 * @property string $enclosure
 * @property string $fields
 * @property integer $skip_duplicate_urls
 * @property integer $skip_duplicate_embeds
 * @property integer $skip_new_categories
 * @property integer $external_images
 * @property string $template
 */
class ImportFeed extends ActiveRecord
{
    /**
     * @var array $option опции для тега select, отвечающего за набор полей csv
     */
    /*protected $options = [
        'skip' => 'Пропустить',
        'video_id' => 'ID видео',
        'title' => 'Название',
        'slug' => 'Слаг',
        'description' => 'Описание',
        'duration' => 'Длительность',
        'embed' => 'Embed код',
        'likes' => 'Лайки',
        'dislikes' => 'Дизлайки',
        'views' => 'Просмотры',
        'published_at' => 'Дата публикации',
        'categories' => 'Категории',
        'categories_ids' => 'Категории (по ID)',
        'images' => 'Скриншоты',
    ];*/

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_import_feeds';
    }

    public function init()
    {
        $this->delimiter = '|';
        $this->enclosure = '"';
        $this->fields = ['skip'];
        $this->skip_first_line = true;
        $this->skip_duplicate_urls = true;
        $this->skip_duplicate_embeds = true;
        $this->skip_new_categories = true;
        $this->external_images = true;
        $this->template = '';

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'description'], 'string', 'max' => 255],
            ['fields', 'each', 'rule' => ['string']],
            [['skip_duplicate_urls', 'skip_duplicate_embeds', 'skip_new_categories', 'external_images', 'skip_first_line'], 'boolean'],
            [['delimiter', 'enclosure'], 'string', 'max' => 16],
            [['template'], 'string', 'max' => 64],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'name' => 'Name',
            'description' => 'Description',
            'delimiter' => 'Delimiter',
            'enclosure' => 'Enclosure',
            'fields' => 'Fields',
            'skip_first_line' => 'Skip First Line',
            'skip_duplicate_urls' => 'Skip Duplicate Urls',
            'skip_duplicate_embeds' => 'Skip Duplicate Embeds',
            'skip_new_categories' => 'Skip New Categories',
            'external_images' => 'External Images',
            'template' => 'Template',
        ];
    }

    public function afterFind()
    {
        $this->fields = json_decode($this->fields);

        return parent::afterFind();
    }

    public function beforeSave($insert)
    {
        if (!parent::beforeSave($insert)) {
            return false;
        }

        $this->fields = json_encode($this->fields);

        return true;
    }

    /**
     * Find import feed by id
     *
     * @param [int] $id
     * @return void
     */
    public static function findById($id)
    {
        return self::find()
            ->where(['feed_id' => (int) $id])
            ->one();
    }

    public function getFieldsOptions()
    {
        return [
            'skip' => 'Пропустить',
            'video_id' => 'ID видео',
            'title' => 'Название',
            'slug' => 'Слаг',
            'description' => 'Описание',
            'duration' => 'Длительность',
            'embed' => 'Embed код',
            'likes' => 'Лайки',
            'dislikes' => 'Дизлайки',
            'views' => 'Просмотры',
            'published_at' => 'Дата публикации',
            'categories' => 'Категории',
            'categories_ids' => 'Категории (по ID)',
            'images' => 'Скриншоты',
        ];
    }
}
