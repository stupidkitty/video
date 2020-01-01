<?php

use yii\helpers\Html;
use yii\widgets\DetailView;

$this->title = Yii::t('videos', 'crop_profiles');
$this->params['subtitle'] = Yii::t('videos', 'info');

$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'crop_profiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $crop->getName();

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($crop->getName()) ?></h3>

        <div class="btn-group pull-right">
            <?= Html::a('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['create'], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-edit text-blue"></i>' . Yii::t('videos', 'edit'), ['update', 'id' => $crop->getId()], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-trash text-red"></i>' . Yii::t('videos', 'delete'), ['delete', 'id' => $crop->getId()], [
                'class' => 'btn btn-default btn-sm',
                'data' => [
                    'confirm' => Yii::t('videos', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="box-body pad">

        <div class="row">
                <?= DetailView::widget([
                    'model' => $crop,
                    'template' => "<tr><th width=\"150\">{label}</th><td>{value}</td></tr>",
                    'attributes' => [
                        'crop_id',
                        'name:ntext',
                        'comment:ntext',
                        'command:ntext',
                        'created_at:datetime',
                    ],
                ]) ?>
        </div>

    </div>
</div>
