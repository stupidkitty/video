<?php

namespace SK\VideoModule\Provider;

use Yii;
use yii\db\Exception;
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
    private SettingsInterface $settings;

    public const RELATED_NUMBER = 12;

    public function __construct(SettingsInterface $settings)
    {
        $this->settings = $settings;
    }

    /**
     * Gets related videos
     *
     * @param int $id Video id
     * @return array Related videos
     * @throws Exception
     */
    public function getModels(int $id): array
    {
        $requiredRelatedNum = $this->settings->get('related_number', static::RELATED_NUMBER, 'videos');

        $related = $this->getStoredVideos($id);

        $relatedNum = \count($related);

        if ($relatedNum < $requiredRelatedNum) {
            $this->findAndSaveRelatedIds($id);
            $related = $this->getStoredVideos($id);
        }

        return $related;
    }

    /**
     * Gets related videos, stored in table
     *
     * @param int $id Video id
     * @return array [][] Related videos
     */
    private function getStoredVideos(int $id): array
    {
        $requiredRelatedNum = $this->settings->get('related_number', static::RELATED_NUMBER, 'videos');

        //SELECT `v`.* FROM `videos_related_map` AS `r` LEFT JOIN `videos` AS `v` ON `v`.`video_id` = `r`.`related_id` WHERE `r`.`video_id`=10
        return Video::find()
            ->asThumbs()
            ->innerJoin(['r' => VideosRelatedMap::tableName()], 'v.video_id = r.related_id')
            ->where(['r.video_id' => $id])
            //->untilNow()
            ->onlyActive()
            ->limit($requiredRelatedNum)
            ->asArray()
            ->all();
    }

    /**
     * Find related videos
     *
     * @param int $id Video id
     * @throws Exception
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
            ->andWhere('`v`.`video_id`<>:video_id', [':video_id' => $id])
            //->andWhere(['<=', 'v.published_at', new Expression('NOW()')])
            ->andWhere(['v.status' => Video::STATUS_ACTIVE])
            ->groupBy('v.video_id')
            ->orderBy(['relevance' => SORT_DESC])
            ->limit($requiredRelatedNum)
            ->asArray()
            ->all();

        if (\count($relatedVideos) < $requiredRelatedNum) {
            $needAddititionNum = $requiredRelatedNum - \count($relatedVideos);
            $categoriesIds = \array_column($video['categories'], 'category_id');
            $excludeVideos = \array_column($relatedVideos, 'video_id');

            $result = Video::find()
                ->select('v.video_id')
                ->distinct()
                ->alias('v')
                ->leftJoin(['vcm' => VideosCategories::tableName()], 'v.video_id = vcm.video_id')
                ->andWhere(['vcm.category_id' => $categoriesIds])
                ->andFilterWhere(['not in', 'v.video_id', $excludeVideos])
                ->andWhere(['v.status' => Video::STATUS_ACTIVE])
                ->orderBy(new Expression('RAND()'))
                ->limit($needAddititionNum)
                ->asArray()
                ->all();
            $relatedVideos = \array_merge($relatedVideos, $result);
        }

        $related = [];

        foreach ($relatedVideos as $relatedVideo) {
            $related[] = [$id, $relatedVideo['video_id']];
        }

        // Удалим старое.
        VideosRelatedMap::deleteAll(['video_id' => $id]);

        // вставим новое
        Yii::$app->db->createCommand()
            ->batchInsert(VideosRelatedMap::tableName(), ['video_id', 'related_id'], $related)
            ->execute();
    }
}
