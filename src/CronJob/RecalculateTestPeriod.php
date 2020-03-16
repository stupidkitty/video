<?php
namespace SK\VideoModule\CronJob;

use RS\Component\Core\Settings\SettingsInterface;
use RS\Module\VisitorModule\Model\Visitor;
use SK\CronModule\Handler\HandlerInterface;
use Yii;
use yii\db\Expression;

/**
 * Устанавливает значения тест периода тумбы и периода пересчета цтр
 * в зависимости  от текущего трафика на сайте.
 */
class RecalculateTestPeriod implements HandlerInterface
{
    public function run(): void
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $uniquesLastDay = Visitor::find()
            ->select(new Expression('COUNT(DISTINCT `ip`)'))
            ->where(['>=', 'first_visit', new Expression('(NOW() - INTERVAL 1 DAY)')])
            ->scalar();

        $options = $this->getValuesByDailyUniqs($uniquesLastDay);

        $settings->set('test_item_period', $options['testPeriod'], 'videos');
        $settings->set('recalculate_ctr_period', $options['recalculateCtrPeroid'], 'videos');
    }

    /**
     * Определяет значения текущих периодов исходя из кол-ва уников\дейли
     *
     * @param integer $uniquesVisitors
     * @return array
     */
    protected function getValuesByDailyUniqs(int $uniquesVisitors = 0): array
    {
        if ($this->between($uniquesVisitors, 0, 1000)) {
            return [
                'testPeriod' => 50,
                'recalculateCtrPeroid' => 250
            ];
        } elseif ($this->between($uniquesVisitors, 1001, 10000)) {
            return [
                'testPeriod' => 150,
                'recalculateCtrPeroid' => 750
            ];
        } elseif ($this->between($uniquesVisitors, 10001, 30000)) {
            return [
                'testPeriod' => 300,
                'recalculateCtrPeroid' => 1500
            ];
        } elseif ($this->between($uniquesVisitors, 30001, 100000)) {
            return [
                'testPeriod' => 400,
                'recalculateCtrPeroid' => 2000
            ];
        } else {
            return [
                'testPeriod' => 500,
                'recalculateCtrPeroid' => 2500
            ];
        }
    }

    /**
     * Входит ли число в промежуток между значениями.
     *
     * @param integer $value
     * @param integer $from
     * @param integer $to
     * @return boolean
     */
    protected function between(int $value, int $from, int $to): bool
    {
        return ($value >= $from && $value <= $to);
    }
}
