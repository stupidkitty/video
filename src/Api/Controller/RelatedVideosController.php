<?php

namespace SK\VideoModule\Api\Controller;

use RS\Component\Core\Settings\SettingsInterface;
use SK\VideoModule\Api\Form\DeleteRelatedForm;
use SK\VideoModule\Cache\PageCache;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosRelatedMap;
use SK\VideoModule\Provider\RelatedProvider;
use Yii;
use yii\base\InvalidConfigException;
use yii\filters\auth\HttpBearerAuth;
use yii\rest\Controller;
use yii\web\NotFoundHttpException;
use yii\web\Request;

/**
 * VideoController
 */
class RelatedVideosController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors(): array
    {
        return [
            /*'corsFilter' => [
                'class' => Cors::class,
            ],*/
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['index', 'options'],
            ],
            'pageCache' => [
                'class' => PageCache::class,
                'enabled' => (bool) Yii::$container->get(SettingsInterface::class)->get('enable_page_cache', false),
                'only' => ['index'],
                'duration' => 7200,
                'dependency' => [
                    'class' => 'yii\caching\TagDependency',
                    'tags' => 'videos:related',
                ],
                'variations' => [
                    Yii::$app->language,
                    \implode(':', \array_values(Yii::$container->get(Request::class)->get())),
                ],
            ],
        ];
    }

    /**
     * Получает список похожих видео у конкретного видео.
     *
     * @param int $id
     * @return array
     */
    public function actionIndex(int $id): array
    {
        $responseData['result']['related'] = [];

        try {
            $video = $this->findById($id);

            $relatedProvider = new RelatedProvider;

            $videos = $relatedProvider->getModels($video->video_id);

            $relatedVideos = \array_map(function ($video) {
                $videoData = [
                    'id' => (int) $video['video_id'],
                    'imageId' => (int) $video['image_id'],
                    'slug' => $video['slug'],
                    'title' => $video['title'],
                    'orientation' => (int) $video['orientation'],
                    'videoPreview' => $video['video_preview'],
                    'duration' => (int) $video['duration'],
                    'likes' => (int) $video['likes'],
                    'dislikes' => (int) $video['dislikes'],
                    'commentsNum' => (int) $video['comments_num'],
                    'isHd' => (int) $video['is_hd'],
                    'views' => (int) $video['views'],
                    'publishedAt' => $video['published_at'],
                ];

                if (!empty($video['poster'])) {
                    $videoData['poster'] = [
                        'id' => (int) $video['poster']['image_id'],
                        'path' => $video['poster']['filepath'],
                        'sourceUrl' => $video['poster']['source_url'],
                    ];
                }

                if (!empty($video['categories'])) {
                    $videoData['categories'] = \array_map(function ($category) {
                        return [
                            'id' => (int) $category['category_id'],
                            'slug' => $category['slug'],
                            'title' => $category['title'],
                            'h1' => $category['h1'],
                        ];
                    }, $video['categories']);
                }

                return $videoData;
            }, $videos);

            $responseData['result']['relatedVideos'] = $relatedVideos;

            return $responseData;
        } catch (\Throwable $e) {
            return $responseData;
        }
    }

    /**
     * Удаляет похожие посты.
     *
     * @param Request $request
     * @param int $id
     * @return array
     * @throws InvalidConfigException
     */
    public function actionDelete(Request $request, int $id = 0): array
    {
        $form = new DeleteRelatedForm();

        if ($form->load($request->getBodyParams()) && $form->isValid()) {
            VideosRelatedMap::deleteAll(['related_id' => $form->related_ids]);
        }

        return [
                'result' => [
                    'deletedRelated' => $form->related_ids
                ]
        ];
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param int $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById(int $id): Video
    {
        $video = Video::find()
            ->alias('v')
            ->whereIdOrSlug((int) $id)
            ->one();

        if (null === $video) {
            throw new NotFoundHttpException('The requested video does not exist.');
        }

        return $video;
    }
}
