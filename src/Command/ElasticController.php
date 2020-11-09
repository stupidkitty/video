<?php


namespace SK\VideoModule\Command;

use Elasticsearch\ClientBuilder;
use SK\VideoModule\Elastic\CategoryIndex;
use SK\VideoModule\Elastic\Elastic;
use SK\VideoModule\Elastic\VideoIndex;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideoInterface;
use yii\console\Controller;
use Yii;

class ElasticController extends Controller
{

    public function actionCreate()
    {
        if (VideoIndex::existsIndex()) {
            VideoIndex::deleteIndex();
        }

        if (CategoryIndex::existsIndex()) {
            CategoryIndex::deleteIndex();
        }

        VideoIndex::createIndex();
        CategoryIndex::createIndex();

        $antiDoubles = ['секс', 'sex', "член", "члена", "молодыми",
            "молодые", "зрелые", 'в', 'на', 'за', "со", "с"];

        foreach ($this->getAndRegroupCategories() as $category) {
            if ($category->id == 11) continue;

            $title = str_replace(',', ' ', $category->title); // rm comma
            $titleLowerCase = mb_convert_case($title, MB_CASE_LOWER, "UTF-8"); // lowercase
            $titleArr = explode(' ', $titleLowerCase); // array

            foreach ($titleArr as $i => $word) {
                if (array_search($word, $antiDoubles) !== false) {
                    unset($titleArr[$i]); // rm doubles
                }
            }

            array_push($antiDoubles, ...$titleArr);

            $category->title = implode(' ', $titleArr);
            CategoryIndex::saveCategory($category);
        }

        $videos = Video::find()->where([
            'status' => VideoInterface::STATUS_ACTIVE
        ])->all();

        foreach ($videos as $video) {
            $elastic = new VideoIndex();
            $elastic->fill($video);
            $elastic->save();
        }

        Yii::info('The ElasticSearch index was created (' . VideoIndex::index() . ').', __METHOD__);

        print 'The ElasticSearch index was created (' . VideoIndex::index() . '). Documents: ' . count($videos) . PHP_EOL;
    }

    public function actionExists()
    {
        print VideoIndex::existsIndex() ? 'true' : 'false';
    }

    public function actionDelete()
    {
        VideoIndex::deleteIndex();
        CategoryIndex::deleteIndex();

        Yii::info('The ElasticSearch index was deleted (' . VideoIndex::index() . ').', __METHOD__);
        print 'The ElasticSearch index was deleted ' . VideoIndex::index() . PHP_EOL;
    }

    public function actionPing()
    {
        print Elastic::client()->ping() ? 'pong' : 'connection error';
    }

    private function getAndRegroupCategories()
    {
        $categories = Category::find()->all();
        $one = [];
        $two = [];
        $more = [];
        foreach ($categories as $category) {
            switch (count(explode(' ', $category->title))) {
                case 1:
                    array_push($one, $category);
                    break;
                case 2:
                    array_push($two, $category);
                    break;
                default:
                    array_push($more, $category);
            }
        }
        $categories = [];
        array_push($categories, ...$one);
        array_push($categories, ...$two);
        array_push($categories, ...$more);
        return $categories;
    }
}
