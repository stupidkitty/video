<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;
use RS\Module\AdminModule\Asset\DatetimePicker;

DatetimePicker::register($this);

$this->title = 'Импорт';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = ['label' => 'Импорт', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Видео';

?>

<?php if ($model->hasErrors('csv_rows')): ?>
    <div class="box box-danger collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title">Ошибки</h3>

            <div class="box-tools pull-right">
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
                <!-- /.box-header -->
        <div class="box-body" style="display: none;">
            <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-exclamation-circle"></i> Следующие записи не были добавлены: </h4>
                <ul>
                <?php foreach ($model->getErrors('csv_rows') as $error): ?>
                    <li><?= $error ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($model->hasNotInsertedRows()): ?>
    <?php $numNotInsertedRows = count($model->getNotInsertedRows()) ?>
    <div class="box box-danger collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title">Не вставленные строки</h3>

            <div class="box-tools pull-right">
                <span data-toggle="tooltip" title="" class="badge bg-red" data-original-title="<?= $numNotInsertedRows ?> строки"><?= $numNotInsertedRows ?></span>
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
                <!-- /.box-header -->
        <div class="box-body" style="display: none;">
            <div class="row">
                <div class="col-md-12 form-group">
                    <?= Html::textarea('csv_not_inserted_rows', implode(PHP_EOL, $model->getNotInsertedRows()), [
                            'id' => 'csv-not-inserted-rows',
                            'class' => 'form-control csv-not-inserted-rows',
                            'rows' => 12
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<?php if ($model->hasNotInsertedIds()): ?>
    <?php $numNotInsertedIds = count($model->getNotInsertedIds()) ?>
    <div class="box box-danger collapsed-box">
        <div class="box-header with-border">
            <h3 class="box-title">Не вставленные иды</h3>

            <div class="box-tools pull-right">
                <span data-toggle="tooltip" title="" class="badge bg-red" data-original-title="<?= $numNotInsertedIds ?> строки"><?= $numNotInsertedIds ?></span>
                <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
                <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
            </div>
        </div>
                <!-- /.box-header -->
        <div class="box-body" style="display: none;">
            <div class="row">
                <div class="col-md-12 form-group">
                    <?= Html::textarea('csv_not_inserted_ids', implode(',', $model->getNotInsertedIds()), [
                            'id' => 'csv-not-inserted-ids',
                            'class' => 'form-control csv-not-inserted-rows',
                            'rows' => 12
                        ]
                    ) ?>
                </div>
            </div>
        </div>
    </div>
<?php endif ?>

<div class="box box-primary">
    <div class="box-header with-border">
        <i class="glyphicon glyphicon-import text-light-violet"></i><h3 class="box-title">Импорт видео</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                <?php $form = ActiveForm::begin([
                        'options' => [
                            'name' => 'preset-select'
                        ],
                        'method' => 'get',
                        'action' => ['import/videos'],
                    ]); ?>
                    Настройки импорта: <?= Html::dropDownList('preset', $preset, $presetListOptions, [
                        'prompt' => 'Default',
                        'id' => 'preset',
                        'class' => 'btn-default btn-sm',
                    ]) ?>
                <?php ActiveForm::end(); ?>
            </div>
            <div class="btn-group">
                <?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['add-feed'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить новый фид']) ?>
                <?php if ($preset > 0): ?>
                    <?= Html::a('<i class="fa fa-edit" style="color:#337ab7;"></i>', ['update-feed', 'id' => $preset], ['class' => 'btn btn-default btn-sm', 'title' => 'Редактировать фид']) ?>
                    <?= Html::a('<i class="fa fa-trash-o" style="color:brown;"></i>', ['delete-feed', 'id' => $preset], [
                        'class' => 'btn btn-default btn-sm',
                        'title' => 'Удалить фид',
                        'data' => [
                            'confirm' => 'Действительно хотите удалить этот фид?',
                            'method' => 'post',
                        ],
                    ]) ?>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <div class="box-body pad">
        <?php $form = ActiveForm::begin([
                'id' => 'video-import-form',
                'options' => [
                    'enctype' => 'multipart/form-data'
                ]
            ])
        ?>
        <h4>Настройки ввода</h4>

        <div class="row">

            <div class="col-md-3 form-group">
                <label class="control-label" style="display:block;">Добавить\удалить поля</label>
                <div class="btn-group">
                    <button type="button" id="add_field" class="btn btn-default"><i class="fa fa-plus"></i></button>
                    <button type="button" id="remove_field" class="btn btn-default"><i class="fa fa-minus"></i></button>
                </div>
            </div>

            <div class="col-md-2 form-group">
                <label class="control-label">Разделитель</label>
                <?= Html::activeInput('text', $model, 'delimiter', ['class' => 'form-control']) ?>
            </div>

            <div class="col-md-2 form-group">
                <label class="control-label">Ограничитель поля</label>
                <?= Html::activeInput('text', $model, 'enclosure', ['class' => 'form-control']) ?>
            </div>
        </div>


        <h4>Поля csv</h4>
        <div class="row csv-fields">
            <?php foreach ($model->fields as $field): ?>
                <div class="col-md-2 form-group">
                    <?= Html::dropDownList('fields[]', $field, $model->getOptions(), ['class' => 'form-control']) ?>
                </div>
            <?php endforeach; ?>
        </div>

        <div class="row">
            <div class="col-md-12 form-group">
                <label class="control-label" for="csv-rows">Данные для вставки (содержимое csv)</label>
                <?= Html::activeTextarea($model, 'csv_rows', ['id' => 'csv-rows', 'class' => 'form-control', 'rows' => 6]) ?>
                <div class="help-block"></div>
            </div>

            <div class="col-md-12 form-group">
                <label for="csv_file" class="control-label">Файл импорта</label>
                <?= Html::fileInput('csv_file', null, ['id' => 'csv_file']) ?>
                <p class="help-block">Убедитесь в соответствии полей файла с текущими настройками.</p>
            </div>
        </div>

        <h3>Дополнительные настройки</h3>

        <div class="row">
            <div class="col-md-3 form-group">
                <label class="control-label">Время публикации</label>
                <div class="input-group date" id="default_date">
                    <?= Html::activeInput('text', $model, 'default_date', ['class' => 'form-control']) ?>
                    <span class="input-group-addon">
                        <span class="glyphicon glyphicon-calendar"></span>
                    </span>
                </div>
                <div class="help-block">Y-m-d H:i:s (eg. 2018-01-16 14:04:46)</div>
            </div>
            <div class="col-md-9 form-group">
                <fieldset id="set_published_at_method">
                    <?php echo Html::activeRadioList($model, 'set_published_at_method', [
                            'now' => 'Текущая (указанная)',
                            'auto_add' => 'Автоматически добавлять с интервалом от крайнего опубликованного поста',
                        ],
                        [
                            'item' => function ($index, $label, $name, $checked, $value) {
                                $radio = Html::radio($name, $checked, ['value' => $value]);
                                $span = Html::tag('span', $label, ['class' => 'radio-item__label']);
                                $label = Html::label("{$radio} {$span}", null, ['class' => 'radio-item radio-item--flex']);

                                return $label;
                            },
                            'label' => false,
                        ]
                    ) ?>
                </fieldset>
                <div class="help-block">Если в CSV указана дата публикации, то эти настройки будут игнорироваться (применится дата публикации из CSV)</div>
            </div>

            <div class="col-md-12 form-group">
                <label class="control-label">Пользователь</label>
                <?= Html::activeDropDownList($model, 'user_id', $userListOptions, ['class' => 'form-control', 'style' => 'width:initial;']) ?>
            </div>
            <div class="col-md-12 form-group">
                <label class="control-label">Статус</label>
                <?= Html::activeDropDownList($model, 'status', $statusListOptions, ['class' => 'form-control', 'style' => 'width:initial;']) ?>
            </div>

            <div class="col-md-12 form-group">
                <label class="control-label">Шаблон для просмотра (по умолчанию используется view)</label>
                <?= Html::activeInput('text', $model, 'template', ['class' => 'form-control', 'style' => 'width:200px']) ?>
            </div>
        </div>

        <hr>

        <div class="row">
            <div class="col-md-12 form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_first_line', ['label' => false]) ?> <span>Пропустить первую строчку</span></label>
                <div class="help-block">Активировать, если в первой строке указаны названия столбцов</div>
            </div>

            <div class="col-md-12 form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_duplicate_urls', ['label' => false]) ?> <span>Пропускать видео с повторяющимися source URL-ами</span></label><br>
                <label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_duplicate_embeds', ['label' => false]) ?> <span>Пропускать видео с повторяющимися embed кодами</span></label>
            </div>
            <div class="col-md-12 form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($model, 'skip_new_categories', ['label' => false]) ?> <span>Запретить создание новых категорий</span></label>
            </div>
            <div class="col-md-12 form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($model, 'external_images', ['label' => false]) ?> <span>Использовать внешние тумбы (не будут скачиваться и нарезаться)</span></label>
            </div>


        </div>

        <?php ActiveForm::end() ?>
        <progress style="display: none;width: 300px;"></progress>
    </div>


    <div class="box-footer clearfix">
        <div class="form-group">
            <?= Html::submitButton('Добавить', ['class' => 'btn btn-default', 'form' => 'video-import-form']) ?>
            <?= Html::a('К списку', ['list-feeds'], ['class' => 'btn btn-warning']) ?>
        </div>
    </div>

</div>

<?php

$rowOptions = [];
foreach ($model->getOptions() as $key => $val) {
    $rowOptions[] = [
        'value' => $key,
        'text' => $val,
    ];
}

$encodedOptions = json_encode($rowOptions);
$this->registerJS("var csvSelectOptions = {$encodedOptions};", \yii\web\View::POS_HEAD, 'csvSelectOptions');

$js = <<< 'JS'
    $('#add_field').click(function() {
        var tagDiv = $('<div/>', {
            class: 'col-md-2 form-group'
        });
        var tagSelect = $('<select/>', {
            class: 'form-control',
            name: 'fields[]'
        });

        $(csvSelectOptions).each(function() {
            tagSelect.append($('<option>').attr('value',this.value).text(this.text));
        });

        tagSelect.appendTo(tagDiv);
        tagDiv.appendTo('.csv-fields');
    });
    $('#remove_field').click(function(){
        var fields_container = $('.csv-fields div');
        var childs_num = fields_container.children().length;

        if (childs_num > 1) {
            fields_container.last().remove();
        }
    });

    $('#preset').on('change', function() {
        document.forms['preset-select'].submit();
    });

    $('#default_date').datetimepicker({
        format: 'YYYY-MM-DD HH:mm:ss',
        locale: 'ru',
        sideBySide: true
    });

    /*var videoForm = document.querySelector('#video-import-form');

    videoForm.addEventListener('submit', function (e) {
        this.onsubmit = function (){return false};
        e.preventDefault();

        var formData = new FormData(this);
        formData.append('file', this.csv_file.files[0]);


        $('progress').toggle();

        $.ajax({
          method: 'POST',
          cache: false,
          processData: false,
          contentType: false,
          enctype: 'multipart/form-data',
          url: this.action,
          data: formData,
          // Custom XMLHttpRequest
          xhr: function() {
              var myXhr = $.ajaxSettings.xhr();
              if (myXhr.upload) {
                  // For handling the progress of the upload
                  myXhr.upload.addEventListener('progress', function(e) {
                      if (e.lengthComputable) {
                          $('progress').attr({
                              value: e.loaded,
                              max: e.total,
                          });
                      }
                  } , false);
              }
              return myXhr;
          }
        }).done(function() {
            $('progress').toggle();
        });
    });*/
JS;

$this->registerJS($js);
