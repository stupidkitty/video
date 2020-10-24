<?php


namespace SK\VideoModule\Command;

use Elasticsearch\ClientBuilder;
use SK\VideoModule\Elastic\Search;
use SK\VideoModule\Model\Video;
use SK\VideoModule\Model\VideoInterface;
use yii\console\Controller;
use Yii;

class ElasticController extends Controller
{

    public function actionCreate()
    {
        if (Search::existsIndex()){
            Search::deleteIndex();
        }

        Search::createIndex();

        $videos = Video::find()->where([
            'status' => VideoInterface::STATUS_ACTIVE
        ])->all();

        foreach ($videos as $video) {
            $elastic = new Search();
            $elastic->fill($video);
            $elastic->save();
        }

        Yii::info('The ElasticSearch index was created (' . Search::index() . ').', __METHOD__);

        print 'The ElasticSearch index was created (' . Search::index() . '). Documents: ' . count($videos) . PHP_EOL;
    }

    public function actionExists()
    {
       print Search::existsIndex() ? 'true' : 'false';
    }

    public function actionDelete()
    {
        Search::deleteIndex();

        Yii::info('The ElasticSearch index was deleted (' . Search::index() . ').', __METHOD__);
        print 'The ElasticSearch index was deleted ' . Search::index() . PHP_EOL;
    }

    public function actionPing()
    {
        print Search::client()->ping() ? 'pong' : 'error';
    }

    public function actionSearch($query)
    {
        $search = new Search();
        foreach ($search->search($query) as $item) {
            print_r($item);
        }
    }
}