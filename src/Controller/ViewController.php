<?php
namespace SK\VideoModule\Controller;

use Yii;
use yii\web\Request;
use yii\web\Response;
use yii\web\Controller;
use yii\filters\PageCache;
use SK\VideoModule\Model\Video;
use yii\base\ViewContextInterface;
use yii\web\NotFoundHttpException;
use SK\VideoModule\Event\VideoShow;
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
                'enabled' => (bool) $this->get(SettingsInterface::class)->get('enable_page_cache', false),
                'only' => ['index'],
                'duration' => 3600,
                'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT 1',
                ],
                'variations' => [
                    Yii::$app->language,
                    \implode(':', \array_values($this->getRequest()->get())),
                    $this->isMobile(),
                ],
            ],
        ];
    }

    /**
     * @inheritdoc
     */
    public function init()
    {
        $request = $this->get(Request::class);
        $response = $this->get(Response::class);

        $response->on($response::EVENT_AFTER_SEND, function () use ($request) {
            Yii::$app->trigger('video-show', new VideoShow([
                'id' => (int) $request->get('id', 0),
                'slug' => $request->get('slug', ''),
            ]));
        });

        Yii::$app->on('video-show', [VideoSubscriber::class, 'registerShow']);

        parent::init();
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
        $settings = $this->get(SettingsInterface::class);

        $identify = (0 !== (int) $id) ? (int) $id : $slug;
        $video = $this->findByIdentify($identify);

        $template = !empty($video['template']) ? $video['template'] : 'view';

        $this->registerXRobotsTag($video);

        if ($settings->get('internal_register_activity', true, 'videos')) {
            $this->on(self::EVENT_AFTER_ACTION, [VideoSubscriber::class, 'onView'], $video);
        }

        return $this->render($template, [
            'settings' => $settings,
            'video' => $video,
        ]);
    }

    /**
     * Find video by id or slug
     *
     * @param integer $id
     * @param string $slug
     * @return null|Video
     * @throws NotFoundHttpException
     */
    protected function findByIdentify($identify)
    {
        $video = Video::find()
            ->alias('v')
            ->withViewRelations()
            ->whereIdOrSlug($identify)
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
        $deviceDetect = $this->get('device.detect');

        return $deviceDetect->isMobile() || $deviceDetect->isTablet();
    }

    /**
     * Get request class form DI container
     *
     * @return \yii\web\Request
     */
    protected function get($name)
    {
        return Yii::$container->get($name);
    }

    /**
     * Регистрирует заголовок для запрета индексации
     * или запрета перехода по ссылкам страницы просмотра видео
     *
     * @param array $video
     * @return void
     */
    protected function registerXRobotsTag(array $video)
    {
        $response = $this->get(Response::class);

        $crawlerRestrictionTypes = [];
        if (true === (bool) $video['noindex']) {
            $crawlerRestrictionTypes[] = 'noindex';
        }

        if (true === (bool) $video['nofollow']) {
            $crawlerRestrictionTypes[] = 'nofollow';
        }

        if (!empty($crawlerRestrictionTypes)) {
            $headers = $response->getHeaders();
            $headers->add('X-Robots-Tag', \implode(',', $crawlerRestrictionTypes));
        }
    }
}
