<?php
namespace SK\VideoModule\Form\Admin;

use yii\base\Model;

/**
 * VideoForm Форма редактирования видео ролика (поста)
 */
class VideoForm extends Model
{
    public $title;
    public $slug;
    public $description;
    public $short_description;
    public $video_url;
    public $source_url;
    public $embed;
    public $template;
    /** @var integer $image_id Идентификатор постера из числа скриншотов видео. */
    public $image_id;
    /** @var integer $user_id Идентификатор автора (владельца). */
    public $user_id;
    public $orientation;
    public $duration;
    public $status;
    public $on_index;
    public $is_hd;
    public $published_at;
    /** @var array $categories_ids Список айди категорий видео ролика. */
    public $categories_ids = [];

    /**
     * @inheritdoc
     */
    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['slug', 'title', 'short_description', 'video_url', 'source_url', 'embed', 'template'], 'string', 'max' => 255],
            [['description'], 'string', 'max' => 3000],
            [['image_id', 'user_id', 'orientation', 'duration', 'status'], 'integer'],
            [['on_index', 'is_hd'], 'boolean'],
            [['published_at'], 'safe'],

            ['categories_ids', 'each', 'rule' => ['integer']],
            ['categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['categories_ids', 'default', 'value' => []],

            [['title', 'description', 'short_description'], 'filter', 'filter' => function ($value) {
                $value = preg_replace('/\s+/', ' ', $value);
                return trim($value);
            }],

            [['slug', 'video_url', 'source_url', 'embed', 'template'], 'trim'],
            ['status', 'default', 'value' => 0],
            ['orientation', 'default', 'value' => 0],
            ['on_index', 'default', 'value' => 1],
        ];
    }

    /**
     * @inheritdoc
     */
	public function formName()
	{
		return '';
	}

    /**
     * Валидирует форму и возвращает результат валидации.
     * true если все правила успешно пройдены.
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->validate();
    }
}
