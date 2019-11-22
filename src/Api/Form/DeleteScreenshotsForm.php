<?php
namespace SK\VideoModule\Api\Form;

use yii\base\Model;

/**
 * ScreenshotsForm Форма для добавления скриншотов вручную.
 */
class DeleteScreenshotsForm extends Model
{
    public $video_id;
    public $screenshots_ids = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['video_id', 'required'],
            ['video_id', 'integer'],

            ['screenshots_ids', 'each', 'rule' => ['integer']],
            ['screenshots_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['screenshots_ids', 'default', 'value' => []],
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
