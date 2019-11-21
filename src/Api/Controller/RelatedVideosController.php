<?php
namespace SK\VideoModule\Api\Controller;

use Yii;
use yii\web\Request;
use yii\rest\Controller;
use yii\filters\PageCache;
use SK\VideoModule\Model\Video;
use yii\web\NotFoundHttpException;
use yii\filters\auth\HttpBearerAuth;
use SK\VideoModule\Provider\RelatedProvider;
use RS\Component\Core\Settings\SettingsInterface;

/**
 * VideoController
 */
class RelatedVideosController extends Controller
{
    /**
     * @inheritdoc
     */
    public function behaviors()
    {
        return [
            'authenticator' => [
                'class' => HttpBearerAuth::class,
                'except' => ['index'],
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
     * @return void
     */
    public function actionIndex($id)
    {
        $responseData['result']['related'] = [];

        try {
            $video = $this->findById($id);
        } catch (NotFoundHttpException $e) {
            return $responseData;
        }

        $relatedProvider = new RelatedProvider;

        $videos = $relatedProvider->getModels($video->video_id);

        $related = [];

        foreach ($videos as $video) {
            $videoData = [
                'video_id' => (int) $video['video_id'],
                'image_id' => (int) $video['image_id'],
                'slug' => $video['slug'],
                'title' => $video['title'],
                'orientation' => (int) $video['orientation'],
                'video_preview' => $video['video_preview'],
                'duration' => (int) $video['duration'],
                'likes' => (int) $video['likes'],
                'dislikes' => (int) $video['dislikes'],
                'comments_num' => (int) $video['comments_num'],
                'is_hd' => (int) $video['is_hd'],
                'views' => (int) $video['views'],
                'published_at' => $video['published_at'],
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

            $related[] = $videoData;
        }

        return $related;
    }

    /**
     * Finds the Video model based on its primary key value.
     * If the model is not found, a 404 HTTP exception will be thrown.
     *
     * @param integer $id
     * @return Video the loaded model
     * @throws NotFoundHttpException if the model cannot be found
     */
    protected function findById($id)
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
