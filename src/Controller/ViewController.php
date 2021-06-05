<?php

namespace SK\VideoModule\Controller;

use RS\Component\Core\Filter\QueryParamsFilter;
use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Cache\PageCache;
use SK\VideoModule\Event\VideoShow;
use SK\VideoModule\EventSubscriber\VideoSubscriber;
use SK\VideoModule\Model\Video;
use Yii;
use yii\base\ViewContextInterface;
use yii\web\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;
use yii\web\Response;

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
                'duration' => 7200,
                /*'dependency' => [
                    'class' => 'yii\caching\DbDependency',
                    'sql' => 'SELECT 1',
                ],*/
                'variations' => [
                    Yii::$app->language,
                    \implode(':', \array_values($this->get(Request::class)->get())),
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
     * @param SettingsInterface $settings
     * @param integer $id
     * @param string $slug
     * @return mixed
     * @throws NotFoundHttpException
     */
    public function actionIndex(SettingsInterface $settings, int $id = 0, string $slug = '')
    {
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
     * @param int|string $identify
     * @return array
     * @throws NotFoundHttpException
     */
    protected function findByIdentify($identify): array
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
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function isMobile(): bool
    {
        $deviceDetect = $this->get('device.detect');

        return $deviceDetect->isMobile();
    }

    /**
     * Get instance by tag name form DI container
     *
     * @param string $name
     * @return object
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function get(string $name): object
    {
        return Yii::$container->get($name);
    }

    /**
     * Регистрирует заголовок для запрета индексации
     * или запрета перехода по ссылкам страницы просмотра видео
     *
     * @param array $video
     * @return void
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\di\NotInstantiableException
     */
    protected function registerXRobotsTag(array $video): void
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
