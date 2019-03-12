<?php
namespace SK\VideoModule\Model;

use yii\db\ActiveRecord;

/**
 * This is the model class for table "videos_crops".
 *
 * @property integer $crop_id
 * @property string $name
 * @property string $comment
 * @property string $command
 * @property string $created_at
 */
class CropProfile extends ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'videos_crops';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['name'], 'required'],
            [['name', 'comment', 'command'], 'string'],
            [['created_at'], 'safe'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->crop_id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = (string) $name;
    }

    /**
     * @inheritdoc
     */
    public function getComment()
    {
        return $this->comment;
    }

    /**
     * @inheritdoc
     */
    public function setComment($comment)
    {
        $this->comment = (string) $comment;
    }

    /**
     * @inheritdoc
     */
    public function getCommand()
    {
        return $this->command;
    }

    /**
     * @inheritdoc
     */
    public function setCommand($command)
    {
        $this->command = (string) $command;
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($created_at)
    {
        $this->created_at = (string) $created_at;
    }
}
