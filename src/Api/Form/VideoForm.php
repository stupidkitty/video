<?php
namespace SK\VideoModule\Api\Form;

use yii\base\Model;
use SK\VideoModule\Model\Video;

/**
 * VideoForm Форма редактирования видео ролика (поста)
 */
class VideoForm extends Model
{
    public $video_id;
    public $title;
    public $slug;
    public $description;
    public $search_field;
    public $video_preview;
    public $embed;
    public $template;
    public $orientation;
    public $duration;
    public $status;
    public $on_index;
    public $is_hd;
    public $noindex;
    public $nofollow;
    public $published_at;
    public $custom1;
    public $custom2;
    public $custom3;
    /** @var array $categories_ids Список айди категорий видео ролика. */
    public $categories_ids = [];
    /** @var array $images Список урлов тумб для видео ролика. */
    public $images = [];

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
            [['slug', 'title', 'video_preview', 'embed', 'template'], 'string', 'max' => 255],
            [['description', 'search_field', 'custom1', 'custom2', 'custom3'], 'string'],
            [['video_id', 'orientation', 'duration', 'status'], 'integer'],
            ['video_id', 'unique', 'targetClass' => Video::class],
            [['on_index', 'is_hd', 'noindex', 'nofollow'], 'boolean'],
            [['published_at'], 'safe'],

            ['categories_ids', 'each', 'rule' => ['integer']],
            ['categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['categories_ids', 'default', 'value' => []],

            ['images', 'each', 'rule' => ['string']],
            ['images', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['images', 'default', 'value' => []],

            [['title', 'description'], 'filter', 'filter' => function ($value) {
                $value = preg_replace('/\s+/', ' ', $value);
                return trim($value);
            }],

            [['slug', 'video_preview', 'embed', 'template'], 'trim'],
            ['status', 'default', 'value' => 0],
            ['orientation', 'default', 'value' => 1],
            ['on_index', 'default', 'value' => 1],
            [['is_hd', 'noindex', 'nofollow'], 'default', 'value' => 0],
            ['published_at', 'default', 'value' => null],
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
