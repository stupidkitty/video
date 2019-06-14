<?php
namespace SK\VideoModule\Form;

use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * FilterForm a video filtering model.
 */
class FilterForm extends Model
{
    public $orientation = 'straight';
    public $t = 'all-time';
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
    public function rules()
    {
        return [
            [['orientation', 'source'], 'string'],
            ['orientation', 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) {
                $values = StringHelper::explode($value, $delimiter = '-', true, true);
                
                \array_walk($values, function (&$value) {
                    $value = \str_ireplace(['straight', 'gay', 'shemale'], [0, 1, 2], $value);
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
    public function formName()
	{
		return '';
    }
    
    /**
     * Check form is valid
     *
     * @return boolean
     */
    public function isValid()
    {
        return $this->validate();
    }
}
