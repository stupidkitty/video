<?php

namespace SK\VideoModule\Admin\Controller;

use SK\VideoModule\Model\Video;
use SK\VideoModule\Statistic\RotationStatisticBuilder;
use SK\VideoModule\Statistic\VideoStatisticBuilder;
use yii\db\Expression;
use yii\filters\AccessControl;
use yii\web\Controller;

class StatsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],
        ];
    }

    /**
     * Отображает статистику ротации. В том числе по категориям.
     *
     * @return string
     */
    public function actionIndex(): string
    {
        $reportBuilder = new RotationStatisticBuilder();
        $report = $reportBuilder->build();
        $videoReportBuilder = new VideoStatisticBuilder();
        $videoReport = $videoReportBuilder->build();

        return $this->render('index', [
            'videoReport' => $videoReport,
            'report' => $report,
        ]);
    }

    /**
     * Рисует график распределения цтр (цтр/кол-во видео)
     * Sql:
     * ```
     * SELECT ROUND(`max_ctr`, 4) as `ctr`, COUNT(*) as `num` FROM `videos`
     * WHERE `max_ctr` > 0
     * GROUP BY `ctr`
     * ```
     *
     * @return string
     */
    public function actionCtrSpreading(): string
    {
        $query = Video::find();
        $rows = $query
            ->select(['ctr' => new Expression('ROUND(`max_ctr`, 4)'), 'num' => new Expression('COUNT(*)')])
            ->where(['>', 'max_ctr', 0])
            ->groupBy('ctr')
            ->asArray()
            ->all();

        return $this->render('ctr-spreading', [
            'labels' => array_column($rows, 'ctr'),
            'values' => array_column($rows, 'num'),
        ]);
    }
}
