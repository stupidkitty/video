<?php
namespace SK\VideoModule\Admin\Form;

use Yii;
use yii\base\Model;

class SettingsForm extends Model
{
    /**
     * @var integer Количество видео роликов на страницу (тумбы в категориях, или в новых, например).
     */
    public $items_per_page = 24;
    /**
     * @var integer Количество кнопок в строке пагинации (можно указать произвольно).
     */
    public $pagination_buttons_count = 7;
    public $recalculate_ctr_period = 2000;
    public $test_item_period = 200;
    public $test_items_percent = 15;
    public $test_items_start = 3;
    /**
     * @var boolean Включить отображение виджета.
     */
    public $related_enable = 0;
    /**
     * @var integer Количество похожих видео.
     */
    public $related_number = 12;
    /**
     * @var boolean Учитывать или нет описание исходного видео при поиске "похожих" видео.
     */
    public $related_allow_description = 0;
    /**
     * @var boolean Учитывать или нет категории исходного видео при поиске "похожих" видео.
     */
    public $related_allow_categories = 0;
    /**
     * @var integer Фиксированный интервал для автопостинга.
     */
    public $autoposting_fixed_interval = 8640;
    /**
     * @var integer Разброс времени для фиксированного интервала.
     */
    public $autoposting_spread_interval = 600;
    /**
     * @var boolean Флажок для проведения скрытой ротации.
     */
    public $internal_register_activity = 1;

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['items_per_page'], 'integer', 'integerOnly' => true, 'min' => 1],
            [['pagination_buttons_count', 'recalculate_ctr_period', 'test_item_period'], 'integer'],
            [['test_items_start'], 'integer', 'integerOnly' => true, 'min' => 3],
            [['test_items_percent'], 'integer', 'integerOnly' => true, 'min' => 0],
            ['test_items_percent', 'validateTestPercent'],
            ['items_per_page', 'minPerPageCheck'],
            [['related_number'], 'integer', 'integerOnly' => true, 'min' => 0],
            [['related_enable', 'related_allow_description', 'related_allow_categories', 'internal_register_activity'], 'boolean'],
            [['autoposting_fixed_interval', 'autoposting_spread_interval'], 'integer'],

            // defaults
            [['items_per_page'], 'default', 'value' => 24],
            [['pagination_buttons_count'], 'default', 'value' => 7],
            [['recalculate_ctr_period'], 'default', 'value' => 2000],
            [['test_item_period'], 'default', 'value' => 200],
            [['test_items_start'], 'default', 'value' => 3],
            [['test_items_percent'], 'default', 'value' => 15],
            [['related_number'], 'default', 'value' => 12],
            [['related_enable'], 'default', 'value' => 0],
            [['related_allow_description'], 'default', 'value' => 0],
            [['related_allow_categories'], 'default', 'value' => 0],
            [['internal_register_activity'], 'default', 'value' => 1],
            [['autoposting_fixed_interval'], 'default', 'value' => 8640],
            [['autoposting_spread_interval'], 'default', 'value' => 600],
        ];
    }

    public function validateTestPercent($attribute, $params, $validator)
    {
        $itemsPerPage = (int) $this->items_per_page;
        $testItemsStart = (int) $this->test_items_start;
        $potentialTestItemsOnPage = $itemsPerPage - $testItemsStart;

        $maxPercent = (int) floor((100 * $potentialTestItemsOnPage) / $itemsPerPage);

        if ($this->$attribute > 0 && $this->$attribute > $maxPercent) {
            $this->addError($attribute, 'Процент тестовых тумб на странице превышает допустимые пределы. Максимальный процент: ' . $maxPercent);
        }
    }

    public function minPerPageCheck($attribute, $params, $validator)
    {
        $itemsPerPage = (int) $this->items_per_page;
        $testItemsStart = (int) $this->test_items_start;
        $testItemsPercent = (int) $this->test_items_percent;

        if ($itemsPerPage < $testItemsStart && $testItemsPercent > 0) {
            $this->addError($attribute, 'Количество тумб на страницу должно быть равно или превышать стартовую тестовую позицию');
        }
    }

    /**
     * Валидирует форму настроек
     *
     * @return bool
     */
    public function isValid()
    {
        return $this->validate();
    }
}
