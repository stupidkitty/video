<?php


namespace SK\VideoModule\Controller;

use SK\VideoModule\Elastic\Search;
use yii\web\Controller;
use Yii;
use yii\web\Request;

class ElasticController extends Controller
{
    public function actionIndex(Request $request)
    {
        $search = new Search();
        $videos = $search->search($request->getQueryParam('q'));
        Yii::$app->response->format = \yii\web\Response::FORMAT_JSON;
        return $videos;
    }

}