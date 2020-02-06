<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_categories_map".
 *
 * @property integer $category_id
 * @property integer $video_id
 *
 * @property Videos $video
 * @property Videos $category
 */
class VideosCategories extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_categories_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'video_id'], 'required'],
            [
                [
                    'category_id',
                    'video_id',
                    'current_index',
                    'current_shows',
                    'current_clicks',
                    'total_shows',
                    'total_clicks',
                ],
                'integer'
            ],
            [['is_tested'], 'boolean'],
            ['ctr', 'number'],
            ['tested_at', 'datetime', 'format' => 'php: Y-m-d H:i:s'],
            ['tested_at', 'default', 'value' => null],
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(Video::class, ['video_id' => 'video_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCategory()
    {
        return $this->hasOne(Category::class, ['category_id' => 'category_id']);
    }
}
