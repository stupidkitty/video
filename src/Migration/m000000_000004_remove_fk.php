<?php

use yii\db\Migration;

class m000000_000004_remove_fk extends Migration
{
    public function safeUp()
    {
        /** VIDEOS_IMAGES */
        $this->dropForeignKey('videos_images_ibfk_1', 'videos_images');

        /** VIDEOS_CATEGORIES_MAP */
        $this->dropForeignKey('videos_categories_map_ibfk_1', 'videos_categories_map');
        $this->dropForeignKey('videos_categories_map_ibfk_2', 'videos_categories_map');
        $this->dropPrimaryKey('PRIMARY', 'videos_categories_map');
        $this->createIndex('category_id', 'videos_categories_map', 'category_id');

        /** VIDEOS_RELATED_MAP */
        $this->dropForeignKey('videos_related_map_ibfk_1', 'videos_related_map');
        $this->dropForeignKey('videos_related_map_ibfk_2', 'videos_related_map');
        $this->dropPrimaryKey('PRIMARY', 'videos_related_map');
        $this->createIndex('video_id', 'videos_related_map', 'video_id');

        /** VIDEOS_STATS */
        $this->dropForeignKey('videos_stats_ibfk_1', 'videos_stats');
        $this->dropForeignKey('videos_stats_ibfk_2', 'videos_stats');
        $this->dropForeignKey('videos_stats_ibfk_3', 'videos_stats');
        $this->dropColumn('videos_stats', 'published_at');
        $this->dropColumn('videos_stats', 'duration');
    }

    public function safeDown()
    {
        /** VIDEOS_IMAGES */
        $this->addForeignKey(
            'videos_images_ibfk_1',
            'videos_images',
            'video_id',
            'videos',
            'video_id',
            'RESTRICT',
            'CASCADE'
        );

        /** VIDEOS_CATEGORIES_MAP */
        $this->dropIndex('category_id', 'videos_categories_map');
        $this->addPrimaryKey('category_id', 'videos_categories_map', ['category_id', 'video_id']);

        $this->addForeignKey(
            'videos_categories_map_ibfk_1',
            'videos_categories_map',
            'category_id',
            'videos_categories',
            'category_id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'videos_categories_map_ibfk_2',
            'videos_categories_map',
            'video_id',
            'videos',
            'video_id',
            'CASCADE',
            'CASCADE'
        );

        /** VIDEOS_RELATED_MAP */
        $this->dropIndex('video_id', 'videos_related_map');
        $this->addPrimaryKey('video_id', 'videos_related_map', ['video_id', 'related_id']);

        $this->addForeignKey(
            'videos_related_map_ibfk_1',
            'videos_related_map',
            'video_id',
            'videos',
            'video_id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'videos_related_map_ibfk_2',
            'videos_related_map',
            'related_id',
            'videos',
            'video_id',
            'CASCADE',
            'CASCADE'
        );

        /** VIDEOS_STATS */
        $this->addColumn('videos_stats', 'published_at', 'timestamp NULL DEFAULT NULL');
        $this->addColumn('videos_stats', 'duration', 'smallint(5) UNSIGNED NOT NULL DEFAULT 0');

        $this->addForeignKey(
            'videos_stats_ibfk_1',
            'videos_stats',
            'category_id',
            'videos_categories',
            'category_id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'videos_stats_ibfk_2',
            'videos_stats',
            'video_id',
            'videos',
            'video_id',
            'CASCADE',
            'CASCADE'
        );
        $this->addForeignKey(
            'videos_stats_ibfk_3',
            'videos_stats',
            'image_id',
            'videos_images',
            'image_id',
            'CASCADE',
            'CASCADE'
        );

        // восстановление удаленных колонок
        $sql = '
            UPDATE `videos_stats` as `vs`
            LEFT JOIN `videos` as `v` ON `v`.`video_id`=`vs`.`video_id`
            SET `vs`.`published_at`=`v`.`published_at`, `vs`.`duration`=`v`.`duration`
        ';
        $this->execute($sql);

        return true;
    }
}
