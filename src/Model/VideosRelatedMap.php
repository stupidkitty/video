<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_related_map".
 *
 * @property integer $video_id
 * @property integer $related_id
 *
 * @property Videos $video
 * @property Videos $related
 */
class VideosRelatedMap extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_related_map';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['video_id', 'related_id'], 'required'],
            [['video_id', 'related_id'], 'integer'],
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
    public function getRelated()
    {
        return $this->hasOne(Video::class, ['video_id' => 'related_id']);
    }
}
