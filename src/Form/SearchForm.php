<?php
namespace SK\VideoModule\Form;

use Yii;
use yii\base\Model;

/**
 * SearchForm represents the model behind the search form about.
 */
class SearchForm extends Model
{
    public $q;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['q'], 'filter', 'filter' => function($value) {
                $value = trim(strip_tags($value));
                return str_replace (['<', '>', '=', '(', ')', ';', '/'], '', $value);
            }],
            [['q'], 'string', 'length' => [3, 80]],
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
