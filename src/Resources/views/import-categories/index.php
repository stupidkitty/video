<?php

use SK\VideoModule\Admin\Form\CategoriesImportForm;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/**
 * @var bool $isProcessed True, data is processed
 * @var int $handledRowsNum Number of handled csv rows
 * @var string[] $errors Errors of failed rows
 * @var CategoriesImportForm $form
 */

$this->title = 'Импорт';
$this->params['subtitle'] = 'Категории видео';

$this->params['breadcrumbs'][] = ['label' => 'Импорт', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Категории видео';

?>

    <div class="box box-primary">
        <div class="box-header with-border">
            <i class="glyphicon glyphicon-import text-light-violet"></i>
            <h3 class="box-title">Импорт категорий</h3>
            <div class="box-tools pull-right">
              <div class="btn-group">
                <?= Html::beginForm(['index'],'get', ['name' => 'preset-select']) ?>
                  Настройки импорта: <?= Html::dropDownList('preset', $preset, $presetListOptions, [
                        'prompt' => 'Default',
                        'id' => 'preset',
                        'class' => 'btn-default btn-sm',
                ]) ?>
                <?= Html::endForm() ?>
              </div>
              <div class="btn-group">
                <?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['categories-import-feeds/create'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить новый фид']) ?>
                <?php if ($preset > 0): ?>
                  <?= Html::a('<i class="fa fa-edit" style="color:#337ab7;"></i>', ['categories-import-feeds/update', 'id' => $preset], ['class' => 'btn btn-default btn-sm', 'title' => 'Редактировать фид']) ?>
                  <?= Html::a('<i class="fa fa-trash-o" style="color:brown;"></i>', ['categories-import-feeds/delete', 'id' => $preset], [
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
            <?php if ($isProcessed): ?>
                <div class="alert alert-success alert-dismissible">
                    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                    <?= \Yii::t('app', '{n,plural,=0{Обработано 0 строк} =1{Обработана 1 строка} one{Обработана # строка} few{Обработаны # строки} many{Обработано # строк} other{Обработано # строк}} ', ['n' => $handledRowsNum]) ?>
                </div>
            <?php endif ?>

            <?php if (\count($errors) > 0): ?>
              <div class="alert alert-danger alert-dismissible">
                <button type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                <h4><i class="icon fa fa-exclamation-circle"></i> Следующие категории не были добавлены\изменены: </h4>

                <ul>
                  <?php foreach ($errors as $error): ?>
                    <li>
                      <?= \nl2br($error) ?>
                    </li>
                  <?php endforeach ?>
                </ul>
              </div>
            <?php endif ?>

            <?php $activeForm = ActiveForm::begin([
                'id' => 'category-import-form',
                'options' => [
                    'enctype' => 'multipart/form-data',
                ],
            ]) ?>

            <h4>Настройки ввода</h4>
            <div class="row">
                <div class="col-md-3">
                    <label class="control-label" style="display:block;">Добавить\удалить поля</label>
                    <div class="btn-group">
                        <button type="button" id="add_field" class="btn btn-default"><i class="fa fa-plus"></i></button>
                        <button type="button" id="remove_field" class="btn btn-default"><i class="fa fa-minus"></i>
                        </button>
                    </div>
                </div>
                <div class="col-md-2">
                    <label class="control-label">Разделитель</label>
                    <?= Html::activeInput('text', $form, 'delimiter', ['class' => 'form-control']) ?>
                </div>
                <div class="col-md-2">
                    <label class="control-label">Ограничитель поля</label>
                    <?= Html::activeInput('text', $form, 'enclosure', ['class' => 'form-control']) ?>
                </div>
            </div>

            <h4>Поля csv (название или ID обязательны)</h4>
            <div class="row csv-fields">
                <?php foreach ($form->fields as $field): ?>
                    <div class="col-md-2 form-group">
                        <?= Html::dropDownList('fields[]', $field, $form->getOptions(), ['class' => 'form-control']) ?>
                    </div>
                <?php endforeach; ?>
            </div>

            <div class="form-group">
                <label for="csv_file">Файл импорта</label>
                <?= Html::fileInput('csv_file', null, ['id' => 'csv_file']) ?>

                <p class="help-block">Убедитесь в соответствии полей файла c текущими настройками.</p>
            </div>

            <h5><b>или</b></h5>

            <div class="form-group">
              <label for="csv_text">CSV текстом</label>
              <?= Html::activeTextarea($form, 'csv_text', [
                      'class' => 'form-control',
                      'style' => 'min-width:100%; max-width: 100%;',
                      'rows' => 6,
              ]) ?>
            </div>

            <div class="form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($form, 'isSkipFirstLine', ['label' => false]) ?>
                    <span>Пропустить первую строчку</span></label>
                <div class="help-block">Активировать, если в первой строке указаны названия столбцов</div>
            </div>

            <h4 style="margin-top: 30px">Опции вставки\замены</h4>
            <div class="form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($form, 'isUpdate', ['label' => false]) ?> <span>Обновить при совпадении id, названия или слага</span></label>
              <div class="help-block">Если опция активна, данные существующих категорий будут изменены. В противном случае любые изменения будут игнорироваться.</div>

            </div>

            <div class="form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($form, 'isEnable', ['label' => false]) ?> <span>Активировать при вставке</span></label>
                <div class="help-block">Вставленные или обновленные категории будут автоматически активированы.</div>
            </div>

            <div class="form-group">
                <label class="checkbox-block"><?= Html::activeCheckbox($form, 'isReplaceSlug', ['label' => false]) ?>
                    <span>Обновить слаг</span></label>
                <div class="help-block">Будет сгенерирован новый слаг из названия, если таковой не указан в полях csv.
                </div>
            </div>

            <?php ActiveForm::end() ?>

        </div>

        <div class="box-footer clearfix">
            <?= Html::submitButton('Добавить', ['class' => 'btn btn-default', 'form' => 'category-import-form']) ?>
            <?= Html::a('К списку', ['list-feeds'], ['class' => 'btn btn-warning']) ?>
        </div>
    </div>

<?php

$rowOptions = [];
foreach ($form->getOptions() as $key => $val) {
    $rowOptions[] = [
        'value' => $key,
        'text' => $val,
    ];
}

$this->registerJsVar('csvSelectOptions', $rowOptions);

$js = <<< 'JS'
    $('#add_field').click(function() {
      const tagDiv = $('<div/>', {
        class: 'col-md-2 form-group'
      });
      const tagSelect = $('<select/>', {
        class: 'form-control',
           name: 'fields[]'
      });
    
      $(csvSelectOptions).each(function(index) {
         tagSelect.append($('<option>').attr('value', this.value).text(this.text));
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

    $('#preset').on('change', function(e) {
      const val = e.target.options[e.target.selectedIndex].value

      if (val === '') {
        e.target.options[e.target.selectedIndex].value = 0
      }

      document.forms['preset-select'].submit();
    });
JS;

$this->registerJS($js);
