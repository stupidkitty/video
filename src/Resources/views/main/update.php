<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use RS\Component\Core\Widget\Select2;
use RS\Component\Core\Widget\DateTimePicker;

$this->title = Yii::t('videos', 'videos');
$this->params['subtitle'] = Yii::t('videos', 'edit');

$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'videos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $video->title;

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($video->title) ?></h3>

        <div class="btn-group pull-right">
            <?= Html::a('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['create'], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-info-circle text-blue"></i>' . Yii::t('videos', 'info'), ['view', 'id' => $video->getId()], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-trash text-red"></i>' . Yii::t('videos', 'delete'), ['delete', 'id' => $video->getId()], [
                'class' => 'btn btn-default btn-sm',
                'data' => [
                    'confirm' => Yii::t('videos', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="box-body pad">

        <?php $activeForm = ActiveForm::begin([
            'id' => 'video-form',
        ]) ?>

        <div class="row">
            <div class="col-md-4">
                <div class="player">
                    <iframe width="342" height="180" src="<?= "{$this->params['video.embed.base_url']}{$video->embed}" ?>" frameborder="0" allowfullscreen="" scrolling="no"></iframe>
                </div>
                <div style="margin-top:15px;">
                    <?= Html::img($video->poster->filepath) ?>
                </div>
                <div style="margin-top:15px;">
                    <a href="<?= $this->params['video.tako.edit_url'] ?><?= $video->getId() ?>" target="_blank" ?>Редактирование на топе</a>
                </div>
            </div>

            <div class="col-md-8">
                <?= $activeForm->field($form, 'title')->textInput(['maxlength' => true]) ?>

                <?= $activeForm->field($form, 'slug')
                    ->textInput(['maxlength' => true])
                    ->hint('Оставить пустым, чтобы сгенерировать заново (транслит названия)')
                ?>

                <?= $activeForm->field($form, 'description')->textarea(['rows' => 5]) ?>

                <?= $activeForm->field($form, 'short_description')->textInput(['maxlength' => true]) ?>

                <?= $activeForm->field($form, 'categories_ids')->widget(
                        Select2::class,
                        [
                            'items' => $categoriesOptionsList,
                            'autoSort' => false,
                            'clientOptions' => [
                                'minimumResultsForSearch' => -1,
                                'placeholder' => 'Выберите категории',
                                'allowClear' => true,
                                //'width' => '100%',
                            ],
                            'options' => [
                                'multiple' => true,
                                'class' => 'form-control',
                            ],
                        ]
                    );
                ?>

                <?= $activeForm->field($form, 'published_at')->widget(
                        DateTimePicker::class,
                        [
                            'clientOptions' => [
                                'format' => 'YYYY-MM-DD HH:mm:ss',
                                'locale' => 'ru',
                                'sideBySide' => true
                            ],
                            'containerOptions' => [
                                'style' => 'max-width: 300px;',
                            ],
                        ]
                    )
                    ->label('Время публикации')
                    ->hint('Y-m-d H:i:s (eg. ' . gmdate('Y-m-d H:i:s') . ')');
                ?>

                <?= $activeForm->field($form, 'user_id')->dropDownList($usersOptionsList, [
                    'style' => 'width:initial;',
                ]) ?>

                <?= $activeForm->field($form, 'duration')->textInput(['placeholder' => 324])->hint('В секундах') ?>

                <?= $activeForm->field($form, 'video_url')->textInput(['maxlength' => true])->hint('Прямой урл до видео файла') ?>

                <?= $activeForm->field($form, 'embed')->textarea(['rows' => 4]) ?>

                <?= $activeForm->field($form, 'orientation')->dropDownList([
                    1 => 'Straight',
                    2 => 'Lesbian',
                    3 => 'Shemale',
                    4 => 'Gay',
                ], [
                    'prompt' => '-- Выбрать --',
                    'style' => 'width:initial;',
                ]) ?>

                <?= $activeForm->field($form, 'status')->dropDownList($statusesOptionsList, [
                    'style' => 'width:initial;',
                ]) ?>

                <?= $activeForm->field($form, 'on_index')->checkbox() ?>

                <?= Html::activeHiddenInput($form, 'image_id') ?>
            </div>
        </div>

        <?php ActiveForm::end() ?>

        <div class="box-footer">
            <div class="row">
                <div class="col-md-2 col-md-offset-4">
                    <?= Html::submitButton('<i class="fa fa-fw fa-check text-green"></i>' . Yii::t('videos', 'save'), ['class' => 'btn btn-default', 'form' => 'video-form']) ?>
                    <?= Html::a('<i class="fa fa-arrow-left"></i> ' . Yii::t('videos', 'back'), Url::previous('actions-redirect'), ['class' => 'btn btn-warning']) ?>
                </div>
            </div>
        </div>



    </div>
</div>
