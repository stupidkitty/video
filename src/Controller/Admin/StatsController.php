<?php
namespace SK\VideoModule\Controller\Admin;

use yii\web\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
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
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'delete' => ['post'],
                ],
            ],
        ];
    }

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
}
