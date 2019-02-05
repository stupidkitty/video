<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */

$this->title = Yii::t('videos', 'videos');
$this->params['subtitle'] = Yii::t('videos', 'categories');

$this->params['breadcrumbs'][] = Yii::t('videos', 'create');

// "jquery sort elements" поиск в гугле, для сортировки элементов при помощи жс.
// Также в импорте категорий нужно сохранять выбранные элементы при помощи локал стораджа

?>

<div class="row">

    <div class="col-md-4">
        <?= $this->render('_left_sidebar', [
            'categories' => $categories,
            'active_id' => 0,
        ]) ?>
    </div>

    <div class="col-md-8">
        <div class="box box-success">
            <div class="box-header with-border">
                <i class="fa fa-file-o"></i><h3 class="box-title">Добавить новую категорию</h3>
                <div class="box-tools pull-right">
                    <div class="btn-group">
                        <?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i> ' . Yii::t('videos', 'import'), ['import/categories'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
                    </div>
                </div>
            </div>

            <div class="box-body pad">

                <?php $activeForm = ActiveForm::begin([
                    'id' => 'category-form',
                    'method' => 'POST',
                    'action' => ['create'],
                ]) ?>

                    <?php echo $this->render('_form_fields', [
                        'form' => $form,
                        'activeForm' => $activeForm,
                    ]) ?>

                <?php ActiveForm::end() ?>

            </div>

            <div class="box-footer clearfix">
			    <div class="form-group">
					<?= Html::submitButton('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['class' => 'btn btn-default', 'form' => 'category-form']) ?>
				</div>
			</div>

        </div>

    </div>
</div>
