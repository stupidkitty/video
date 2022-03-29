<?php
namespace SK\VideoModule\Provider;

use Yii;
use yii\db\Expression;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideosRelatedMap;
use SK\VideoModule\Model\VideosCategories;
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

    /**
     * Gets related videos
     *
     * @param int $id Video id
     * @return array Related videos
     */
    public function getModels(int $id): array
    {
        $requiredRelatedNum = $this->settings->get('related_number', static::RELATED_NUMBER, 'videos');

        $related = $this->getFromTable($id);

        $relatedNum = \count($related);

        if ($relatedNum < $requiredRelatedNum) {
            $this->findAndSaveRelatedIds($id);
            $related = $this->getFromTable($id);
        }

        return $related;
    }

    /**
     * Gets related videos, stored in table
     *
     * @param int $id Video id
     * @return array Reloated videos
     */
    private function getFromTable(int $id): array
    {
        $requiredRelatedNum = $this->settings->get('related_number', static::RELATED_NUMBER, 'videos');

        //SELECT `v`.* FROM `videos_related_map` AS `r` LEFT JOIN `videos` AS `v` ON `v`.`video_id` = `r`.`related_id` WHERE `r`.`video_id`=10
        $videos = Video::find()
            ->asThumbs()
            ->innerJoin(['r' => VideosRelatedMap::tableName()], 'v.video_id = r.related_id')
            ->where(['r.video_id' => $id])
            //->untilNow()
            ->onlyActive()
            ->limit($requiredRelatedNum)
            ->asArray()
            ->all();

        return $videos;
    }

    /**
     * Find related videos
     *
     * @param int $id Video id
     * @throws \yii\db\Exception
     */
    private function findAndSaveRelatedIds(int $id): void
    {
        $allowCategories = $this->settings->get('related_allow_categories', false, 'videos');
        $allowDescription = $this->settings->get('related_allow_description', false, 'videos');
        $requiredRelatedNum = $this->settings->get('related_number', static::RELATED_NUMBER, 'videos');

        $query = Video::find()
            ->select(['video_id', 'title', 'description'])
            ->where(['video_id' => $id])
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
            $searchString = trim($video['title'] . ' ' . $video['description']);
        } else {
            $searchString = trim($video['title']);
        }

        $relatedModels = Video::find()
            ->select(['`v`.`video_id`', 'MATCH (`search_field`) AGAINST (:query) AS `relevance`'])
            ->from (['v' => Video::tableName()]);

        if ($allowCategories && !empty($video['categories'])) {
                // выборка всех идентификаторов категорий поста.
            $categoriesIds = \array_column($video['categories'], 'category_id');
            $relatedModels
                ->leftJoin(['vcm' => VideosCategories::tableName()], 'v.video_id = vcm.video_id')
                ->andWhere(['vcm.category_id' => $categoriesIds]);
        }

        $relatedVideos = $relatedModels
            ->andWhere('MATCH (`search_field`) AGAINST (:query)', [':query' => $searchString])
            ->andWhere('`v`.`video_id`<>:video_id', [':video_id' => $video['video_id']])
            //->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
            ->andWhere(['v.status' => Video::STATUS_ACTIVE])
            ->groupBy('v.video_id')
            ->orderBy(['relevance' => SORT_DESC])
            ->limit($requiredRelatedNum)
            ->asArray()
            ->all();

        if (\count($relatedVideos) === 0) {
            return;
        }

        $related = [];

        foreach ($relatedVideos as $relatedVideo) {
            $related[] = [$video['video_id'], $relatedVideo['video_id']];
        }

        // Удалим старое.
        VideosRelatedMap::deleteAll(['video_id' => $video['video_id']]);

        // вставим новое
        Yii::$app->db->createCommand()
            ->batchInsert(VideosRelatedMap::tableName(), ['video_id', 'related_id'], $related)
            ->execute();
    }
}
