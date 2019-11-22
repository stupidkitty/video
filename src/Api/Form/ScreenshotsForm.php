<?php
namespace SK\VideoModule\Api\Form;

use yii\base\Model;

/**
 * ScreenshotsForm Форма для добавления скриншотов вручную.
 */
class ScreenshotsForm extends Model
{
    public $video_id;
    public $screenshots = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['video_id', 'required'],
            ['video_id', 'integer'],

            ['screenshots', 'each', 'rule' => [
                'each', 'rule' => ['string'],
            ]],
            ['screenshots', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['screenshots', 'default', 'value' => []],
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
