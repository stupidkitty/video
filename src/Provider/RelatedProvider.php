<?php
namespace SK\VideoModule\Provider;

use Yii;
use yii\db\Expression;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosRelatedMap;
use SK\VideoModule\Model\VideosCategoriesMap;
use RS\Component\Core\Settings\SettingsInterface;

/**
 * RelatedFinder содержит методы для поиска похожих роликов.
 */
class RelatedProvider
{
    private $requiredRelatedNum;

    private $settings;

    const RELATED_NUMBER = 12;

    public function __construct()
    {
        $this->settings = Yii::$container->get(SettingsInterface::class);
    }

    public function getFromTable($video_id)
    {
        $requiredRelatedNum = $this->settings->get('related_number', self::RELATED_NUMBER, 'videos');
            //SELECT `v`.* FROM `videos_related_map` AS `r` LEFT JOIN `videos` AS `v` ON `v`.`video_id` = `r`.`related_id` WHERE `r`.`video_id`=10
        $videos = Video::find()
            ->select(['v.video_id', 'v.image_id', 'v.slug', 'v.title', 'v.orientation', 'v.video_preview', 'v.duration', 'v.likes', 'v.dislikes', 'v.comments_num', 'v.views', 'v.template', 'v.published_at'])
            ->from(['v' => Video::tableName()])
            ->leftJoin(['r' => VideosRelatedMap::tableName()], 'v.video_id = r.related_id')
            ->with(['categories' => function ($query) {
                $query->select(['category_id', 'title', 'slug', 'h1']);
            }])
            ->with(['poster' => function ($query) {
                $query->select(['image_id', 'video_id', 'filepath', 'source_url']);
            }])
            ->where(['r.video_id' => $video_id])
            ->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
            ->andWhere(['v.status' => Video::STATUS_ACTIVE])
            ->limit($requiredRelatedNum)
            ->asArray()
            ->all();

        return $videos;
    }

    public function getModels($video_id)
    {
        $requiredRelatedNum = $this->settings->get('related_number', self::RELATED_NUMBER, 'videos');

        $related = $this->getFromTable($video_id);

        $relatedNum = count($related);

        if (empty($related) || $relatedNum < $requiredRelatedNum) {
            $this->findAndSaveRelatedIds($video_id);
            $related = $this->getFromTable($video_id);
        }

        return $related;
    }

    public function findAndSaveRelatedIds($video_id)
    {
        $allowCategories = $this->settings->get('related_allow_categories', false, 'videos');
        $allowDescription = $this->settings->get('related_allow_description', false, 'videos');
        $requiredRelatedNum = $this->settings->get('related_number', self::RELATED_NUMBER, 'videos');

        $query = Video::find()
            ->select(['video_id', 'title', 'description', 'short_description'])
            ->where(['video_id' => $video_id])
            ->asArray();

        if ($allowCategories) {
            $query
                ->with(['categories' => function ($query) {
                    $query->select(['category_id'])
                        ->where(['enabled' => 1]);
                }]);
        }

        $video = $query->one();

        if (null === $video) {
            return;
        }

        if ($allowDescription) {
            $searchString = trim($video['title'] . ' ' . $video['description'] . ' ' . $video['short_description']);
        } else {
            $searchString = trim($video['title']);
        }

        $relatedModels = Video::find()
            ->select(['`v`.`video_id`', 'MATCH (`title`, `description`, `short_description`) AGAINST (:query) AS `relevance`'])
            ->from (['v' => Video::tableName()]);

        if ($allowCategories && !empty($video['categories'])) {
                // выборка всех идентификаторов категорий поста.
            $categoriesIds = array_column($video['categories'], 'category_id');
            $relatedModels
                ->leftJoin(['vcm' => VideosCategoriesMap::tableName()], 'v.video_id = vcm.video_id')
                ->andWhere(['vcm.category_id' => $categoriesIds]);
        }

        $relatedVideos = $relatedModels
            ->andWhere('MATCH (`title`, `description`, `short_description`) AGAINST (:query)', [':query' => $searchString])
            ->andWhere('`v`.`video_id`<>:video_id', [':video_id' => $video['video_id']])
            ->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
            ->andWhere(['v.status' => Video::STATUS_ACTIVE])
            ->groupBy('v.video_id')
            ->orderBy(['relevance' => SORT_DESC])
            ->limit($requiredRelatedNum)
            ->asArray()
            ->all();

        if (is_array($relatedVideos)) {
            $related = [];

            foreach ($relatedVideos as $relatedVideo) {
                $related[] = [$video['video_id'], $relatedVideo['video_id']];
            }
            //dump($related); exit;
                // Удалим старое.
            Yii::$app->db->createCommand()
                ->delete(VideosRelatedMap::tableName(), ['video_id' => $video['video_id']])
                ->execute();
                // вставим новое
            Yii::$app->db->createCommand()
                ->batchInsert(VideosRelatedMap::tableName(), ['video_id', 'related_id'], $related)
                ->execute();
        }
    }
}
