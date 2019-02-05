<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

/* @var $this yii\web\View */
/* @var $model backend\models\videos\VideosCategories */

$this->title = 'Категории видео';
$this->params['subtitle'] = 'Импорт';

$this->params['breadcrumbs'][] = ['label' => 'Категории видео', 'url' => ['index']];
$this->params['breadcrumbs'][] = 'Импорт';

?>

<div class="row">
	<div class="col-md-12">

		<div class="box box-primary">
			<div class="box-header with-border">
				<i class="glyphicon glyphicon-import"></i><h3 class="box-title">Импорт категорий</h3>
				<div class="box-tools pull-right">
					<div class="btn-group">
						<?= Html::a('<i class="fa fa-plus" style="color:green;"></i>', ['create'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить категорию']) ?>
					</div>
				</div>
            </div>

			<?php $form = ActiveForm::begin(['options' => ['enctype' => 'multipart/form-data']]); ?>

	            <div class="box-body pad">

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
							<input type="text" name="delimiter" class="form-control" value="|">
            			</div>
						<div class="col-md-2 form-group">
							<label class="control-label">Ограничитель поля</label>
							<input type="text" name="enclosure" class="form-control" value="&quot;">
            			</div>
					</div>

					<h4>Поля csv</h4>
					<div class="row csv-fields">
						<div class="col-md-2 form-group">
							<select class="form-control" name="fields[]">
								<?php foreach ($model->getOptions() as $option): ?>
									<?php echo "<option value=\"{$option['value']}\">{$option['text']}</option>" ?>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<div class="row">
						<div class="col-md-12 form-group">
							<label class="control-label" for="csv-rows">Данные для категорий</label>
							<textarea id="csv-rows" class="form-control" name="csv_rows" rows="6" aria-invalid="false"></textarea>

							<div class="help-block"></div>
						</div>

						<div class="col-md-12 form-group">
							<label for="csv_file">Файл импорта</label>
							<?= Html::fileInput('csv_file', null, ['id' => 'csv_file']) ?>

							<p class="help-block">Убедитесь в соответствии полей файла и текущими настройками.</p>
						</div>

						<div class="col-md-12 form-group">
							<input type="hidden" name="replace" value="0">
							<label><input type="checkbox" name="replace" value="1" aria-invalid="false"> Обновить при совпадении id или названия</label>
							<div class="help-block">Если опция не активна, при совпадении идентификатора или названия импортируемая категория будет игнорироваться.</div>
						</div>
					</div>

				</div>


				<div class="box-footer clearfix">
				    <div class="form-group">
						<?= Html::submitButton('Добавить', ['class' => 'btn btn-primary']) ?>
						<?= Html::a('Назад', ['index'], ['class' => 'btn btn-warning']) ?>
					</div>
				</div>

			<?php ActiveForm::end(); ?>

		</div>

	</div>
</div>

<?php

$encodedOptions = json_encode($model->getOptions());

$this->registerJS("
var arr = {$encodedOptions};

\$('#add_field').click(function(){
	var cont = \$('<div/>', {
	    class: 'col-md-2 form-group'
	});
	var slct = \$('<select/>', {
	    class: 'form-control',
	    name: 'fields[]'
	});
	$(arr).each(function() {
		slct.append($('<option>').attr('value',this.value).text(this.text));
	});

	slct.appendTo(cont);
	cont.appendTo('.csv-fields');
});
\$('#remove_field').click(function(){
	\$('.csv-fields div').last().remove();
});
");
?>