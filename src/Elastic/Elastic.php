<?php


namespace SK\VideoModule\Elastic;

use Elasticsearch\ClientBuilder;


class Elastic
{
    /**
     * Get elasticsearch client
     * @return \Elasticsearch\Client
     */
    public static function client()
    {
        return ClientBuilder::create()
            ->setHosts(\Yii::$app->params['elasticsearch']['hosts'])
            ->build();
    }

}
