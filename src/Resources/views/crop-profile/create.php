<?php

use yii\helpers\Html;

$this->title = Yii::t('videos', 'crop_profiles');
$this->params['subtitle'] = Yii::t('videos', 'create');

$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'crop_profiles'), 'url' => ['index']];
$this->params['breadcrumbs'][] = Yii::t('videos', 'create');

?>

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($this->title) ?></h3>
    </div>

    <div class="box-body pad">
        <?= $this->render('_form', [
            'form' => $form,
        ]) ?>
    </div>

    <div class="box-footer clearfix">
        <div class="row">
            <div class="col-md-2 col-md-offset-4">
                <?= Html::submitButton('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['class' => 'btn btn-default', 'form' => 'crop-form']) ?>
                <?= Html::a('<i class="fa fa-fw fa-reply text-orange"></i>' . Yii::t('videos', 'back'), ['index'], ['class' => 'btn btn-default'])?>
            </div>
        </div>
    </div>
</div>
