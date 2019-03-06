<?php
namespace SK\VideoModule\Controller\Api;

use Yii;
use yii\filters\Cors;
use yii\db\Expression;
use yii\rest\Controller;
use yii\filters\VerbFilter;
use yii\filters\AccessControl;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use yii\filters\auth\HttpBearerAuth;
use RS\Component\Core\Settings\SettingsInterface;

/**
 * InfoController
 */
class InfoController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            /*'access' => [
                'class' => AccessControl::class,
                'only' => ['index'],
                'rules' => [
                    [
                        'actions' => ['index'],
                        'allow' => true,
                        'roles' => ['@'],
                    ],
                ],
            ],*/
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'index' => ['get'],
                ],
            ],
            'corsFilter' => [
                'class' => Cors::class,
            ],
            'authenticator' => [
                'class' => HttpBearerAuth::class,
            ],
        ];
    }

    /**
     * Gets info about auto postig. Max date post and count future posts.
     * @return array
     */
    public function actionIndex()
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        $data = [];

        $data['total_videos_num'] = Video::find()->count();
        $data['active_videos_num'] = Video::find()->onlyActive()->count();

        $data['autoposting_queue_num'] = Video::find()
            ->andWhere(['>=', 'published_at', new Expression('NOW()')])
            ->onlyActive()
            ->count();

        $data['active_categories_num'] = Category::find()->where(['enabled' => 1])->count();
        $data['total_categories_num'] = Category::find()->count();

        $data['max_published_at'] = Video::find()->onlyActive()->max('published_at');
        $data['autoposting_interval'] = $settings->get('autoposting_fixed_interval', 8640, 'videos');
        $data['autoposting_dispersion_interval'] = $settings->get('autoposting_spread_interval', 600, 'videos');

        return $data;
    }
}
