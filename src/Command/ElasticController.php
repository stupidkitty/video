<?php


namespace SK\VideoModule\Command;

use Elasticsearch\ClientBuilder;
use SK\VideoModule\Elastic\Elastic;
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

        Elastic::createIndex();

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