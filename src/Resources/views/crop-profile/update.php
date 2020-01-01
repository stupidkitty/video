<?php

use yii\helpers\Html;

$this->title = Yii::t('videos', 'crop_profiles');
$this->params['subtitle'] = Yii::t('videos', 'update');

$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'crop_profiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('videos', 'update');

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($crop->getName()) ?></h3>

        <div class="btn-group pull-right">
            <?= Html::a('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['create'], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-info-circle text-blue"></i>' . Yii::t('videos', 'info'), ['view', 'id' => $crop->getId()], ['class' => 'btn btn-default btn-sm']) ?>
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
        <?= $this->render('_form', [
            'form' => $form,
        ]) ?>
    </div>

    <div class="box-footer">
        <div class="row">
            <div class="col-md-2 col-md-offset-4">
                <?= Html::submitButton('<i class="fa fa-fw fa-check text-green"></i>' . Yii::t('videos', 'save'), ['class' => 'btn btn-default', 'form' => 'crop-form']) ?>
                <?= Html::a('<i class="fa fa-arrow-left"></i> ' . Yii::t('videos', 'back'), ['index'], ['class' => 'btn btn-warning']) ?>
            </div>
        </div>
    </div>
</div>
