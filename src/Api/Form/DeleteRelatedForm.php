<?php
namespace SK\VideoModule\Api\Form;

use yii\base\Model;

/**
 * DeleteRelatedForm Форма для удаления похожих видео.
 */
class DeleteRelatedForm extends Model
{
    public $video_id;
    public $related_ids = [];

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            ['video_id', 'required'],
            ['video_id', 'integer'],

            ['related_ids', 'each', 'rule' => ['integer']],
            ['related_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['related_ids', 'default', 'value' => []],
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
