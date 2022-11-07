<?php
namespace SK\VideoModule\Form;

use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * FilterForm a video filtering model.
 */
class FilterForm extends Model
{
    public $orientation;
    public string $t = 'all-time';
    public $durationMin;
    public $durationMax;
    public $isHd;
    public $source;
    public $o;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules(): array
    {
        return [
            [['orientation', 'source'], 'string'],
            ['orientation', 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) {
                $values = StringHelper::explode($value, $delimiter = '-', true, true);

                \array_walk($values, function (&$value) {
                    $value = \str_ireplace(['straight', 'lesbian', 'shemale', 'gay'], [1, 2, 3, 4], $value);
                });

                return $values;
            }],

            [['durationMin', 'durationMax'], 'integer'],

            ['t', 'in', 'range' => ['daily', 'weekly', 'monthly', 'yearly', 'all-time']],
            ['t', 'default', 'value' => 'all-time'],

            [['isHd'], 'boolean'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function formName(): string
    {
		return '';
    }

    /**
     * Check form is valid
     *
     * @return bool
     */
    public function isValid(): bool
    {
        return $this->validate();
    }
}
