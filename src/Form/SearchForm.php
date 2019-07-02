<?php
namespace SK\VideoModule\Form;

use Yii;
use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * SearchForm represents the model behind the search form about.
 */
class SearchForm extends Model
{
    public $q;
    public $orientation = 'straight';

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q'], 'filter', 'filter' => function($value) {
                $value = \trim(\strip_tags($value));
                return \str_replace(['<', '>', '=', '(', ')', ';', '/'], '', $value);
            }],
            [['q'], 'string', 'length' => [3, 80]],

            [['orientation'], 'string'],
            ['orientation', 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) {
                $values = StringHelper::explode($value, $delimiter = '-', true, true);
                
                \array_walk($values, function (&$value) {
                    $value = \str_ireplace(['straight', 'gay', 'shemale'], [0, 1, 2], $value);
                });
                
                return $values;
            }],
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

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'q' => Yii::t('app', 'Search'),
        ];
    }

    /**
     * @inheritdoc
     */
    public function getQuery()
    {
        return $this->q;
    }
}
