<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_images".
 *
 * @property integer $screenshot_id
 * @property integer $video_id
 * @property string $path
 * @property string $source_url
 * @property string $created_at
 *
 * @property Video $video
 */
class Screenshot extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_screenshots';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['video_id'], 'integer'],
            [['path', 'source_url'], 'string'],
            [['created_at'], 'safe'],

            ['video_id', 'default', 'value' => 0],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->screenshot_id;
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getVideo()
    {
        return $this->hasOne(Video::class, ['video_id' => 'video_id']);
    }
}
