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
    public $change_user;
    public $change_status;
    public $add_categories;
    public $delete_categories;

    public $videos_ids;
    public $user_id;
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
                    'change_user',
                    'change_status',
                    'add_categories',
                    'delete_categories',
                ], 'boolean'
            ],

            [['user_id', 'status'], 'integer'],
            ['user_id', 'exist', 'targetClass' => User::class, 'skipOnEmpty' => true],

            ['add_categories_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true ],
            ['add_categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],

            ['delete_categories_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true ],
            ['delete_categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],

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
                if ('1' === $this->change_user) {
                    $video->user_id = $this->user_id;
                }
                // Изменение статуса
                if ('1' === $this->change_status && !empty($this->status)) {
                    $video->status = $this->status;
                }

                $video->save();
                // Добавление категории
                if ('1' === $this->add_categories) {
                    $categories = Category::find()
                        ->where(['category_id' => $this->add_categories_ids])
                        ->all();

                    foreach ($categories as $category) {
                        $video->addCategory($category);
                    }
                }
                // Удаление категории
                if ('1' === $this->delete_categories) {
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
