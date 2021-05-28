<?php

use yii\helpers\Html;
use yii\web\View;
use yii\widgets\ActiveForm;

/**
 * @var yii\web\View $this
 * @var SK\VideoModule\Model\CategoryImportFeed $feed
 */

$this->title = 'Фиды импорта';
$this->params['subtitle'] = 'Добавление фида импорта категорий';

$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['categories/create']];
$this->params['breadcrumbs'][] = 'Добавление фида импорта категорий';

?>

<div class="box box-success">
  <div class="box-header with-border">
    <i class="fa fa-file-o text-purple-disabled"></i>
    <h3 class="box-title">Добавление фида импорта категорий</h3>
  </div>

  <?php $form = ActiveForm::begin() ?>

  <div class="box-body pad">

    <?= $form->field($feed, 'name')->textInput(['maxlength' => true]) ?>

    <?= $form->field($feed, 'description')->textInput(['maxlength' => true]) ?>

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
        <?= Html::activeTextInput($feed, 'delimiter', ['class' => 'form-control']) ?>
      </div>
      <div class="col-md-2 form-group">
        <label class="control-label">Ограничитель поля</label>
        <?= Html::activeTextInput($feed, 'enclosure', ['class' => 'form-control']) ?>
      </div>
    </div>

    <h4>Поля csv</h4>
    <div class="row csv-fields">
      <?php foreach ($feed->fields as $field): ?>
        <div class="col-md-2 form-group">
          <?= Html::dropDownList('CategoryImportFeed[fields][]', '', $feed->getFieldsOptions(), ['class' => 'form-control']) ?>
        </div>
      <?php endforeach ?>
    </div>

    <div class="form-group">
      <label class="checkbox-block"><?= Html::activeCheckbox($feed, 'skip_first_line', ['label' => false]) ?>
        <span>Пропустить первую строчку</span></label>
      <div class="help-block">Активировать, если в первой строке указаны названия столбцов</div>
    </div>

    <h4 style="margin-top: 30px">Опции вставки\замены</h4>
    <div class="form-group">
      <label class="checkbox-block"><?= Html::activeCheckbox($feed, 'update_exists', ['label' => false]) ?> <span>Обновить при совпадении id, названия или слага</span></label>
      <div class="help-block">Если опция активна, данные существующих категорий будут изменены. В противном случае любые изменения будут игнорироваться.</div>
    </div>

    <div class="form-group">
      <label class="checkbox-block"><?= Html::activeCheckbox($feed, 'activate', ['label' => false]) ?> <span>Активировать при вставке</span></label>
      <div class="help-block">Вставленные или обновленные категории будут автоматически активированы.</div>
    </div>

    <div class="form-group">
      <label class="checkbox-block"><?= Html::activeCheckbox($feed, 'update_slug', ['label' => false]) ?>
        <span>Обновить слаг</span></label>
      <div class="help-block">Будет сгенерирован новый слаг из названия, если таковой не указан в полях csv.</div>
    </div>
  </div>


  <div class="box-footer clearfix">
    <div class="form-group">
      <?= Html::submitButton('Добавить', ['class' => 'btn btn-success']) ?>
      <?= Html::a('К списку', ['index'], ['class' => 'btn btn-warning']) ?>
    </div>
  </div>

  <?php ActiveForm::end() ?>

</div>

<?php

$rowOptions = [];
foreach ($feed->getFieldsOptions() as $key => $val) {
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
           name: 'CategoryImportFeed[fields][]'
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
JS;

$this->registerJS($js);
