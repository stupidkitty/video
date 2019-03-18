<?php
namespace SK\VideoModule\Controller\Admin;

use yii\web\Controller;
use yii\filters\AccessControl;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Statistic\VideoStatisticBuilder;
use SK\VideoModule\Statistic\RotationStatisticBuilder;

class StatsController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
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
     * @return mixed
     */
    public function actionIndex()
    {
        $reportBuilder = new RotationStatisticBuilder;
        $report = $reportBuilder->build();
        $videoReportBuilder = new VideoStatisticBuilder;
        $videoReport = $videoReportBuilder->build();

        return $this->render('index', [
            'videoReport' => $videoReport,
            'report' => $report,
        ]);
    }

    /**
     * Рисует график распределения цтр (цтр/кол-во видео)
     * 
     * Sql:
     * ```
     * SELECT ROUND(`max_ctr`, 4) as `ctr`, COUNT(*) as `num` FROM `videos` 
     * WHERE `max_ctr` > 0
     * GROUP BY `ctr`
     * ```
     *
     * @return mixed
     */
    public function actionCtrSpreading()
    {
        $query = Video::find();
        $rows = $query
            ->select(['ctr' => new \yii\db\Expression('ROUND(`max_ctr`, 4)'), 'num' => new \yii\db\Expression('COUNT(*)')])
            ->where(['>', 'max_ctr', 0])
            ->groupBy('ctr')
            ->asArray()
            ->all();

        return $this->render('ctr-spreading', [
            'labels' => array_column($rows, 'ctr'),
            'values' =>  array_column($rows, 'num'),
        ]); 
    }
}
