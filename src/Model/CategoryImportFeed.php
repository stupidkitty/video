<?php

namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_categories_import_feeds".
 *
 * @property integer $feed_id
 * @property string $name
 * @property string $delimiter
 * @property string $enclosure
 * @property array $fields
 * @property bool $skip_first_line
 * @property bool $update_exists
 * @property bool $activate
 * @property bool $update_slug
 * @property string $updated_at
 * @property string $created_at
 */
class CategoryImportFeed extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%videos_categories_import_feeds}}';
    }

    public function init()
    {
        $this->delimiter = ',';
        $this->enclosure = '"';
        $this->fields = ['skip'];
        $this->skip_first_line = 1;
        $this->update_exists = 0;
        $this->activate = 1;
        $this->update_slug = 0;

        parent::init();
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'description', 'delimiter', 'enclosure'], 'string', 'max' => 255],
            ['fields', 'each', 'rule' => ['string']],
            [['skip_first_line', 'update_exists', 'update_slug', 'activate'], 'boolean'],
        ];
    }

    /**
     * Find import feed by id
     *
     * @param [int] $id
     * @return void
     */
    public static function findById(int $id)
    {
        return self::find()
            ->where(['feed_id' => $id])
            ->one();
    }

    public function getFieldsOptions()
    {
        return [
            'skip' => 'Пропустить',
            'category_id' => 'ID категории',
            'title' => 'Название',
            'slug' => 'Слаг',
            'meta_title' => 'Мета заголовок',
            'meta_description' => 'Мета описание',
            'h1' => 'Заголовок H1',
            'description' => 'Описание',
            'seotext' => 'СЕО текст',
            'param1' => 'Доп. поле 1',
            'param2' => 'Доп. поле 2',
            'param3' => 'Доп. поле 3',
        ];
    }
}
