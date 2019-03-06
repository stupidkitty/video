<?php
namespace SK\VideoModule\Controller;

use Yii;
use yii\web\Controller;
use yii\filters\PageCache;
use yii\base\ViewContextInterface;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Model\Video;
use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\EventSubscriber\VideoSubscriber;

/**
 * ViewController implements the CRUD actions for Videos model.
 */
class ViewController extends Controller implements ViewContextInterface
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'queryParams' => [
                'class' => QueryParamsFilter::class,
                'actions' => [
                    'index' => ['id', 'slug'],
                ],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                //'only' => ['index'],
                'duration' => 3600,
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT 1',
                ],
                'variations' => [
                    Yii::$app->language,
                    $this->getRequest()->get('id', 1),
                    $this->getRequest()->get('slug', 1),
                    $this->isMobile(),
                ],
            ],
        ];
    }

    /**
     * Переопределяет дефолтный путь шаблонов модуля.
     * Путь задается в конфиге модуля, в компонентах приложения.
     *
     * @return string
     */
    public function getViewPath()
    {
        return $this->module->getViewPath();
    }

    /**
     * Показывает страницу просмотра видео.
     *
     * @param integer $id
     * @param string $slug
     *
     * @return mixed
     */
    public function actionIndex($id = 0, $slug = '')
    {
        $settings = Yii::$container->get(SettingsInterface::class);

        if (0 !== (int) $id) {
            $video = $this->findById($id);
        } elseif (!empty($slug)) {
            $video = $this->findBySlug($slug);
        } else {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        $template = !empty($video['template']) ? $video['template'] : 'view';

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(self::EVENT_AFTER_ACTION, [VideoSubscriber::class, 'onView'], $video);
        }

        return $this->render($template, [
            'settings' => $settings,
            'video' => $video,
        ]);
    }

    /**
     * Find video by slug
     *
     * @param string $slug
     *
     * @return null|Video
     *
     * @throws NotFoundHttpException
     */
    protected function findBySlug($slug)
    {
        $video = Video::find()
            ->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }])
            ->with(['images' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url'])
                    ->indexBy('image_id');
            }])
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1'])
                    ->where(['enabled' => 1]);
            }])
            ->where(['slug' => $slug])
            ->untilNow()
            ->onlyActive()
            ->asArray()
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $video;
    }

    /**
     * Find video by id
     *
     * @param integer $id
     *
     * @return null|Video
     *
     * @throws NotFoundHttpException
     */
    protected function findById($id)
    {
        $video = Video::find()
            ->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }])
            ->with(['images' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url'])
                    ->indexBy('image_id');
            }])
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1'])
                    ->where(['enabled' => 1]);
            }])
            ->where(['video_id' => $id])
            ->untilNow()
            ->onlyActive()
            ->asArray()
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested page does not exist.');
        }

        return $video;
    }

    /**
     * Detect user is mobile device
     *
     * @return boolean
     */
    protected function isMobile()
    {
        $deviceDetect = Yii::$container->get('device.detect');
        
        return $deviceDetect->isMobile() || $deviceDetect->isTablet();
    }

    /**
     * Get request class form DI container
     *
     * @return \yii\web\Request
     */
    protected function getRequest()
    {
        return Yii::$container->get(Request::class);
    }
}
