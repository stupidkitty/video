<?php
namespace SK\VideoModule\Api\Controller;

use Yii;
use yii\filters\Cors;
use yii\db\Expression;
use yii\rest\Controller;
use yii\filters\VerbFilter;
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
    public function behaviors(): array
    {
        return [
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
     * Gets info about auto posting. Max date post and count future posts.
     *
     * @param SettingsInterface $settings
     * @return array
     */
    public function actionIndex(SettingsInterface $settings): array
    {
        $data = [];

        $data['total_videos_num'] = Video::find()->count();
        $data['active_videos_num'] = Video::find()
            ->alias('v')
            ->onlyActive()
            ->count();

        $data['autoposting_queue_num'] = Video::find()
            ->alias('v')
            ->andWhere(['>=', 'published_at', new Expression('NOW()')])
            ->onlyActive()
            ->count();

        $data['active_categories_num'] = Category::find()->where(['enabled' => 1])->count();
        $data['total_categories_num'] = Category::find()->count();

        $data['max_published_at'] = Video::find()
            ->alias('v')
            ->onlyActive()
            ->max('published_at');
        $data['autoposting_interval'] = $settings->get('autoposting_fixed_interval', 8640, 'videos');
        $data['autoposting_dispersion_interval'] = $settings->get('autoposting_spread_interval', 600, 'videos');

        return $data;
    }
}
