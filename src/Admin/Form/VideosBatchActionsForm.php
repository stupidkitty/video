<?php
namespace SK\VideoModule\Admin\Form;

use yii\base\Model;

use RS\Component\User\Model\User;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;

class VideosBatchActionsForm extends Model
{
    /**
    * Checkboxes
    */
    public $isChangeUser;
    public $isChangeStatus;
    public $isAddCategories;
    public $isDeleteCategories;
    public $isChangeOrientation;

    public $videos_ids;
    public $user_id;
    public $orientation;
    public $status;
    public $add_categories_ids;
    public $delete_categories_ids;

    public function __construct($config = [])
    {
        parent::__construct($config = []);
    }

    public function rules()
    {
        return [
            [
                [
                    'isChangeUser',
                    'isChangeStatus',
                    'isAddCategories',
                    'isDeleteCategories',
                    'isChangeOrientation',
                ], 'boolean'
            ],

            [['user_id', 'status', 'orientation'], 'integer'],
            ['user_id', 'exist', 'targetClass' => User::class, 'skipOnEmpty' => true],

            ['add_categories_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true ],
            ['add_categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['add_categories_ids', 'default', 'value' => []],

            ['delete_categories_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true ],
            ['delete_categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],
            ['delete_categories_ids', 'default', 'value' => []],

            ['videos_ids', 'each', 'rule' => ['integer']],
            ['videos_ids', 'filter', 'filter' => 'array_filter'],
            ['videos_ids', 'required', 'message' => 'Videos not selected'],
        ];
	}

    /**
     * @inheritdoc
     */
    public function formName()
    {
        return '';
	}

    /**
     * @inheritdoc
     */
    public function handle()
    {
        $videosQuery = Video::find()
            ->with('categories')
            ->with('images')
            ->where(['video_id' => $this->videos_ids]);

        foreach ($videosQuery->batch(20) as $videos) {
            foreach ($videos as $video) {
                // Изменение пользователя
                if ((bool) $this->isChangeUser && is_numeric($this->user_id)) {
                    $video->user_id = (int) $this->user_id;
                }

                // Изменение ориентации
                if ((bool) $this->isChangeOrientation && is_numeric($this->orientation)) {
                    $video->orientation = (int) $this->orientation;
                }

                // Изменение статуса
                if ((bool) $this->isChangeStatus && is_numeric($this->status)) {
                    $video->status = (int) $this->status;
                }

                $video->save();

                // Добавление категории
                if ((bool) $this->isAddCategories) {
                    $categories = Category::find()
                        ->where(['category_id' => $this->add_categories_ids])
                        ->all();

                    foreach ($categories as $category) {
                        $video->addCategory($category);
                    }
                }

                // Удаление категории
                if ((bool) $this->isDeleteCategories) {
                    $categories = Category::find()
                        ->where(['category_id' => $this->delete_categories_ids])
                        ->all();

                    foreach ($categories as $category) {
                        $video->removeCategory($category);
                    }
                }
            }
        }
	}

    /**
     * Возвращает текстовые названия статусов в виде массива [статус => название].
     *
     * @return array;
     */
    public function getStatuses()
    {
        return Video::getStatuses();
	}

    /**
     * Возвращает текстовые названия статусов в виде массива [статус => название].
     *
     * @return array;
     */
    public function getUsers()
    {
        return User::find()
            ->select( 'username')
            ->indexBy('user_id')
            ->column();
	}

    /**
     * Возвращает массив категорий в виде: ['category_id' => 'title'].
     *
     * @return array;
     */
    public function getCategories()
    {
        return Category::find()
            ->select('title')
            ->indexBy('category_id')
            ->column();
    }
}
