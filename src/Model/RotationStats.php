<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_stats".
 *
 * @property integer $category_id
 * @property integer $image_id
 * @property integer $video_id
 * @property integer $best_image
 * @property string $published_at
 * @property integer $duration
 * @property integer $shows
 * @property integer $clicks
 * @property double $ctr
 *
 * @property Video $video
 * @property Category $category
 * @property Image $image
 */
class RotationStats extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_stats';
    }
    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['category_id', 'image_id', 'video_id'], 'required'],
            [
                [
                    'category_id',
                    'image_id',
                    'video_id',
                    'current_shows',
                    'current_clicks',
                    'shows0',
                    'clicks0',
                    'shows1',
                    'clicks1',
                    'shows2',
                    'clicks2',
                    'shows3',
                    'clicks3',
                    'shows4',
                    'clicks4',
                ],
                'integer'
            ],
            [['best_image', 'tested_image'], 'boolean'],
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
    /**
     * @return \yii\db\ActiveQuery
     */
    public function getImage()
    {
        return $this->hasOne(Image::class, ['image_id' => 'image_id']);
    }
    /**
     * @inheritdoc
     */
    public static function addVideo(Category $category, Video $video, Image $image, $isBest = false)
    {
        $exists = self::find()
            ->where(['video_id' => $video->getId(), 'category_id' => $category->getId(), 'image_id' => $image->getId()])
            ->exists();

        if ($exists)
            return true;

        $rotationStats = new static();

        $rotationStats->video_id = $video->getId();
        $rotationStats->category_id = $category->getId();
        $rotationStats->image_id = $image->getId();
        $rotationStats->best_image = 0;

        if (true === (bool) $isBest) {
            $rotationStats->best_image = 1;
        } elseif ($image->getId() === $video->image_id) {
        	$rotationStats->best_image = 1;
        }

        return $rotationStats->save();
    }
}
