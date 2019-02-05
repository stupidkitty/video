<?php
namespace SK\VideoModule\Form\Admin;

use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class CategoryForm extends Model
{
	public $title;
	public $slug;
	public $meta_title;
	public $meta_description;
	public $h1;
	public $description;
	public $seotext;
	public $param1;
	public $param2;
	public $param3;
	public $on_index;
	public $enabled;

    public function __construct($config = [])
    {
		parent::__construct($config);

		/*$this->category = $category;

		$this->title = '';
		$this->slug = '';
		$this->meta_title = '';
		$this->meta_description = '';
		$this->h1 = '';
		$this->description = '';
		$this->seotext = '';
		$this->param1 = '';
		$this->param2 = '';
		$this->param3 = '';
		$this->on_index = 1;
		$this->reset_clicks_period = 2000;
		$this->enabled = 0;
		$this->attributes = $category->toArray();*/
    }

    /**
     * @inheritdoc
     */
    public function formName()
    {
    	return '';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title'], 'required'],
            [['title', 'slug', 'meta_title', 'meta_description', 'h1', 'description', 'seotext', 'param1', 'param2', 'param3'], 'string'],
            [['on_index', 'enabled'], 'boolean'],

            [['title', 'slug', 'meta_title', 'meta_description', 'h1', 'description', 'seotext', 'param1', 'param2', 'param3'], 'trim'],
            [['meta_title', 'meta_description', 'h1'] , 'filter', 'filter' => function ($attribute) {
                return StringHelper::truncate($attribute, 255, false);
            }],
        ];
    }

    /**
     * @inheritdoc
     */
    public function isValid()
    {
        return $this->validate();
    }
    /*public function save()
	{
		$currentDateTime = gmdate('Y-m-d H:i:s');

		$this->category->title = StringHelper::truncate($this->title, 255, false);

		if ('' === $this->slug) {
			$this->category->generateSlug($this->title);
		} else {
			$this->category->slug = $this->slug;
		}

		$this->category->meta_title = StringHelper::truncate($this->meta_title, 255, false);
		$this->category->meta_description = StringHelper::truncate($this->meta_description, 255, false);
		$this->category->h1 = StringHelper::truncate($this->h1, 255, false);
		$this->category->description = $this->description;
		$this->category->seotext = $this->seotext;
		$this->category->param1 = $this->param1;
		$this->category->param2 = $this->param2;
		$this->category->param3 = $this->param3;
		$this->category->on_index = (bool) $this->on_index;
		$this->category->reset_clicks_period = (int) $this->reset_clicks_period;
		$this->category->setEnabled($this->enabled);
		$this->category->updated_at = $currentDateTime;

		if ($this->category->isNewRecord) {
			$this->category->created_at = $currentDateTime;
		}

		return $this->category->save();
	}*/
}
