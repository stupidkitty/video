<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_images".
 *
 * @property integer $image_id
 * @property integer $video_id
 * @property string $filehash
 * @property integer $position
 * @property string $filepath
 * @property string $source_url
 * @property integer $status
 * @property string $created_at
 *
 * @property Video $video
 * @property RotationStats[] $rotationStats
 */
class Image extends ActiveRecord implements ImageInterface
{
    protected $file;

    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_images';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['video_id', 'position', 'status'], 'integer'],
            [['filepath', 'source_url', 'filehash'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->image_id;
    }

    /**
     * @inheritdoc
     */
    public function setFile(\SplFileInfo $file)
    {
    	$this->file = $file;
    }

    /**
     * @inheritdoc
     */
    public function getFile()
    {
    	return $this->file;
    }
}
