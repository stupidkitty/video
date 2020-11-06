<?php


namespace SK\VideoModule\Command;

use Elasticsearch\ClientBuilder;
use SK\VideoModule\Elastic\Elastic;
use SK\VideoModule\Model\Category;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideoInterface;
use yii\console\Controller;
use Yii;

class ElasticController extends Controller
{

    public function actionCreate()
    {
        if (Elastic::existsIndex()){
            Elastic::deleteIndex();
        }

        if (Elastic::existsCategoriesIndex()){
            Elastic::deleteCategoriesIndex();
        }

        Elastic::createIndex();
        Elastic::createCategoriesIndex();

        $categories = Category::find()->all();
        print count($categories);
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

        $antiDoubles = ['секс', 'sex'];
        foreach ($categories as $category) {
            $title = str_replace(',', ' ', $category->title); //удаление запятой
            $titleLowerCase = mb_convert_case($title, MB_CASE_LOWER, "UTF-8"); // lowercase
            $titleArr = explode(' ', $titleLowerCase); // array
            foreach ($titleArr as $i => $word) {
                if (array_search($word, $antiDoubles) !== false) {
                    unset($titleArr[$i]);
                }

            }

            array_push($antiDoubles, ...$titleArr);

            $category->title = implode(' ', $titleArr);
            Elastic::saveCategory($category);
        }
        print count($categories);

        $videos = Video::find()->where([
            'status' => VideoInterface::STATUS_ACTIVE
        ])->all();

        foreach ($videos as $video) {
            $elastic = new Elastic();
            $elastic->fill($video);
            $elastic->save();
        }

        Yii::info('The ElasticSearch index was created (' . Elastic::index() . ').', __METHOD__);

        print 'The ElasticSearch index was created (' . Elastic::index() . '). Documents: ' . count($videos) . PHP_EOL;
    }

    public function actionExists()
    {
       print Elastic::existsIndex() ? 'true' : 'false';
    }

    public function actionDelete()
    {
        Elastic::deleteIndex();

        Yii::info('The ElasticSearch index was deleted (' . Elastic::index() . ').', __METHOD__);
        print 'The ElasticSearch index was deleted ' . Elastic::index() . PHP_EOL;
    }

    public function actionPing()
    {
        print Elastic::client()->ping() ? 'pong' : 'error';
    }
}