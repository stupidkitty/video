<?php

use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\Videos */
/* @var $form yii\widgets\ActiveForm */
?>

<div class="videos-form">

    <?php $form = ActiveForm::begin(); ?>

    <?= $form->field($model, 'image_id')->textInput() ?>

    <?= $form->field($model, 'user_id')->textInput() ?>

    <?= $form->field($model, 'slug')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'title')->textInput(['maxlength' => true]) ?>

    <?= $form->field($model, 'description')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'orientation')->textInput() ?>

    <?= $form->field($model, 'duration')->textInput() ?>

    <?= $form->field($model, 'embed')->textarea(['rows' => 3]) ?>

    <?= $form->field($model, 'likes')->textInput() ?>

    <?= $form->field($model, 'dislikes')->textInput() ?>

    <?= $form->field($model, 'comments_num')->textInput() ?>

    <?= $form->field($model, 'is_hd')->checkbox() ?>

    <?= $form->field($model, 'on_index')->checkbox() ?>

    <?= $form->field($model, 'views')->textInput() ?>

    <?= $form->field($model, 'status')->textInput() ?>

    <?= $form->field($model, 'published_at')->textInput() ?>

    <?= $form->field($model, 'custom1')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'custom2')->textarea(['rows' => 4]) ?>

    <?= $form->field($model, 'custom3')->textarea(['rows' => 4]) ?>

    <div class="form-group">
        <?= Html::submitButton($model->isNewRecord ? Yii::t('videos', 'Create') : Yii::t('videos', 'Update'), ['class' => $model->isNewRecord ? 'btn btn-success' : 'btn btn-primary']) ?>
    </div>

    <?php ActiveForm::end(); ?>

</div>
