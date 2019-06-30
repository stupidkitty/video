<?php
namespace SK\VideoModule\Admin\Form;

use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * CropProfileForm Форма редактирования профиля кропа
 */
class CropProfileForm extends Model
{
    public $name;
    public $comment;
    public $command;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name', 'comment', 'command'], 'string'],
            ['name', 'match', 'pattern' => '/^[a-z0-9-]\w*$/i'],

            [['comment', 'command'], 'filter', 'filter' => function ($value) {
                return preg_replace('/\s+/', ' ', $value);
            }],

            [['comment'] , 'filter', 'filter' => function ($attribute) {
                return StringHelper::truncate($attribute, 255, false);
            }],

            [['name', 'comment', 'command'], 'trim'],

            [['name', 'command'], 'required'],
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
