<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

use ytubes\videos\admin\models\finders\CategoryFinder;
use ytubes\videos\models\VideoStatus;
use common\models\users\User;

/* @var $this yii\web\View */
/* @var $searchModel app\models\videos\VideoSearch */
/* @var $form yii\widgets\ActiveForm */

if (!empty($filterForm->videos_ids)) {
    $filterForm->videos_ids = implode(',', $filterForm->videos_ids);
}
//$searchModel->categories_ids = implode(',', $searchModel->categories_ids);

/*$user = Yii::$app->user->identity;*/

?>

<div class="box">
    <div class="box-header with-border">
        <h3 class="box-title">Фильтр поиска</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse" data-toggle="tooltip" title="Collapse"><i class="fa fa-minus"></i></button>
            <button type="button" class="btn btn-box-tool" data-widget="remove" data-toggle="tooltip" title="Remove"><i class="fa fa-times"></i></button>
        </div>
    </div>

    <?php $form = ActiveForm::begin([
        'action' => ['index'],
        'method' => 'get',
    ]); ?>

    <div class="box-body">

        <div class="row show-grid">
            <div class="col-md-6">
                <div class="form-group">
                    <label for="videofinder-title">Название, описание</label>
                    <?= Html::activeInput('text', $filterForm, 'title', ['class' => 'form-control']) ?>
                </div>
            </div>

            <div class="col-md-6">
                <div class="form-group">
                    <label for="videofinder-videos_ids">ID (через запятую)</label>
                    <?= Html::activeInput('text', $filterForm, 'videos_ids', ['class' => 'form-control']) ?>
                </div>
            </div>
        </div>

        <div class="row show-grid">
            <div class="col-md-2">
                <div class="form-group">
                    <label for="videofinder-user_id">Автор</label>
                    <?= Html::activeDropDownList($filterForm, 'user_id', $filterForm->getUsers(), ['class' => 'form-control', 'prompt' => '-- Любой --']) ?>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label for="videofinder-status">Статус</label>
                    <?= Html::activeDropDownList($filterForm, 'status', $filterForm->getStatuses(), ['class' => 'form-control', 'prompt' => '-- Любой --']) ?>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label for="videofinder-status">Категория</label>
                    <?= Html::activeDropDownList($filterForm, 'category_id', $filterForm->getCategories(), ['class' => 'form-control', 'prompt' => '-- Все --']) ?>
                </div>
            </div>

            <div class="col-md-2">
                <div class="form-group">
                    <label for="videofinder-per_page">Видео на странице</label>
                    <?= Html::activeInput('text', $filterForm, 'per_page', ['class' => 'form-control']) ?>
                </div>
            </div>
        </div>

    </div>
    <!-- /.box-body -->

    <div class="box-footer">
        <div class="col-md-3 col-md-offset-1">
            <div class="form-group">
                <?= Html::submitButton('Применить', ['class' => 'btn btn-primary']) ?>
                <?= Html::a('Сброс', ['index'], ['class' => 'btn btn-default']) ?>
            </div>
        </div>
    </div>
    <!-- /.box-footer-->
    <?php ActiveForm::end(); ?>
</div>
