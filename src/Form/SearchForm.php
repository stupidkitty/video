<?php

namespace SK\VideoModule\Form;

use yii\base\Model;
use yii\helpers\StringHelper;

/**
 * SearchForm represents the model behind the search form about.
 */
class SearchForm extends Model
{
    /**
     * @var string
     */
    public $q = '';
    /**
     * @var string
     */
    public $orientation;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q'], 'filter', 'filter' => function ($value) {
                $value = \trim(\strip_tags($value));
                return \str_replace(['<', '>', '=', '(', ')', ';', '/'], '', $value);
            }],
            [['q'], 'string', 'length' => [3, 80]],

            [['orientation'], 'string'],
            ['orientation', 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) {
                $values = StringHelper::explode($value, $delimiter = '-', true, true);

                \array_walk($values, function (&$value) {
                    $value = \str_ireplace(['straight', 'lesbian', 'shemale', 'gay'], [1, 2, 3, 4], $value);
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
     * Gets filtered user query
     *
     * @return string
     */
    public function getQuery(): string
    {
        return $this->q;
    }
}
