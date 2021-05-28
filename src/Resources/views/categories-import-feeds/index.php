<?php

use yii\grid\ActionColumn;
use yii\grid\GridView;
use yii\helpers\Html;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = 'Фиды импорта';
$this->params['subtitle'] = 'Список фидов импорта категорий';

$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['categories/create']];
$this->params['breadcrumbs'][] = 'Список фидов';

?>

<div class="box box-default">
  <div class="box-header with-border">
    <i class="fa fa-list text-maroon-disabled"></i>
    <h3 class="box-title">Фиды импорта</h3>
    <div class="box-tools pull-right">
      <div class="btn-group">
        <?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['create'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить новый фид']) ?>
      </div>
    </div>
  </div>

  <div class="box-body pad">
    <?= GridView::widget([
            'dataProvider' => $dataProvider,
            'columns' => [
                    [
                            'attribute' => 'feed_id',
                            'label' => 'Ид фида',
                            'contentOptions' => ['style' => 'width: 90px;'],
                    ],
                    [
                            'attribute' => 'name',
                            'label' => 'Название',
                            'format' => 'raw',
                            'value' => function ($data) {
                              return Html::a($data->name, ['update', 'id' => $data->feed_id], ['title' => 'Редактировать']);
                            },
                            'contentOptions' => ['style' => 'width: 150px;'],
                    ],
                    [
                            'attribute' => 'description',
                            'label' => 'Описание',
                            'format' => 'ntext',
                    ],
                    [
                            'class' => ActionColumn::class,
                            'template' => '
                                <ul class="action-buttons pull-right">
                                    <li class="action-buttons__item">{update}</li>
                                    <li class="action-buttons__item">{delete}</li>
                                </ul>
                            ',
                            'buttons' => [
                                    'update' => function ($url, $model, $key) {
                                      return Html::a('<span class="glyphicon glyphicon-pencil"></span>', ['import/update-feed', 'id' => $model->feed_id], ['title' => 'Редактировать']);
                                    },
                                    'delete' => function ($url, $model, $key) {
                                      return Html::a('<span class="glyphicon glyphicon-trash text-red"></span>', ['import/delete-feed', 'id' => $model->feed_id], [
                                              'title' => 'Удалить фид',
                                              'data' => [
                                                      'confirm' => 'Действительно хотите удалить этот фид?',
                                                      'method' => 'POST',
                                              ],
                                      ]);
                                    },
                            ],
                            'headerOptions' => ['style' => 'width:90px;'],
                    ],
            ],
    ]); ?>

  </div>

</div>
