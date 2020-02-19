<?php
namespace SK\VideoModule\Admin\Form;

use Yii;
use yii\base\Model;
use yii\helpers\StringHelper;
use yii\data\ActiveDataProvider;

use RS\Component\User\Model\User;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\VideosCategories;

/**
 * VideoFinder represents the model behind the search form about `ytubes\videos\admin\models\Video`.
 */
class VideoFilterForm extends Model
{
    public $per_page = 50;
    public $videos_ids = '';
    public $category_id;
    public $user_id;
    public $status;
    public $title;
    public $is_hd;

    public $show_thumb = false;

    public $bulk_edit = false;

    public function __construct($config = [])
    {
        parent::__construct($config);
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'status', 'per_page', 'category_id'], 'integer'],
            [['show_thumb', 'bulk_edit', 'is_hd'], 'boolean'],

            ['videos_ids', 'filter', 'skipOnEmpty' => true, 'filter' => function ($value) {
                return StringHelper::explode($value, ',', true, true);
            }],
            ['videos_ids', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true],
            ['videos_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],

            //['category_id', 'each', 'rule' => ['integer'], 'skipOnEmpty' => true ],
            //['categories_ids', 'filter', 'filter' => 'array_filter', 'skipOnEmpty' => true],

            [['title'], 'string'],
            ['title', 'filter', 'filter' => 'trim', 'skipOnEmpty' => true],
        ];
    }
	public function formName()
	{
		return '';
	}
    /**
     * Получает ролики постранично в разделе "все", отсортированные по дате.
     */
    public function search($params)
    {
        $query = Video::find()
        	->alias('v');

        $dataProvider = new ActiveDataProvider([
            'query' => $query,
            'pagination' => [
                'defaultPageSize' => $this->per_page,
                'pageSize' => $this->per_page,
            ],
            'sort'=> [
                'defaultOrder' => [
                    'published_at' => SORT_DESC,
                ],
            ],
        ]);

        $this->load($params);

        if (!$this->validate()) {
            $query->where('1=0');

            return $dataProvider;
        }

        $dataProvider->pagination->defaultPageSize = $this->per_page;
        $dataProvider->pagination->pageSize = $this->per_page;

        if ($this->title) {
            $query
                ->select(['v.*', 'MATCH (`v`.`title`, `v`.`description`) AGAINST (:query) AS `relevance`'])
                ->where('MATCH (`v`.`title`, `v`.`description`) AGAINST (:query IN BOOLEAN MODE)', [
                    ':query'=> $this->title,
                ])
                ->orderBy(['relevance' => SORT_DESC]);
        }

		if (!empty($this->category_id)) {
			$query->leftJoin(['vcm' => VideosCategories::tableName()], '`v`.`video_id` = `vcm`.`video_id`');
		}

        $query->andFilterWhere([
            'v.video_id' => $this->videos_ids,
            'v.user_id' => $this->user_id,
            'v.status' => $this->status,
            'vcm.category_id' => $this->category_id,
        ]);

        return $dataProvider;
    }
    /**
     * @inheritdoc
     */
    public function getUsers()
    {
    	return User::find()
    		->select('username')
    		->indexBy('user_id')
    		->column();
    }
    /**
     * @inheritdoc
     */
    public function getCategories()
    {
    	return Category::find()
    		->select('title')
    		->indexBy('category_id')
    		->column();
    }
    /**
     * @inheritdoc
     */
    public function getStatuses()
    {
    	return [
    		Video::STATUS_DISABLED => Yii::t('videos', 'status_disabled'),
    		Video::STATUS_ACTIVE => Yii::t('videos', 'status_active'),
    		Video::STATUS_MODERATE => Yii::t('videos', 'status_moderate'),
    		Video::STATUS_DELETED => Yii::t('videos', 'status_deleted'),
    	];
    }
}
