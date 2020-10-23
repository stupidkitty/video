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

    public function actionSpam()
    {
        $videos = Video::find()->all();

        for($i = 0; $i < 10; $i++) {
            foreach ($videos as $video) {
                $elastic = new Search();
                $elastic->fill($video, false);
                $elastic->save();
            }
            print 'push';
        }
    }

    public function actionDelete()
    {
        Search::deleteIndex();

        Yii::info('The ElasticSearch index was deleted (' . Search::index() . ').', __METHOD__);
        print 'The ElasticSearch index was deleted ' . Search::index() . PHP_EOL;
    }

    public function actionPing()
    {
        print Search::client()->ping();
    }

    public function actionSearch($query)
    {
        $search = new Search();
        foreach ($search->search($query) as $item) {
            print 'title: ' . $item['_source']['title'] . PHP_EOL;
            print 'description: ' . $item['_source']['description'] . PHP_EOL . PHP_EOL;
        }
    }
}