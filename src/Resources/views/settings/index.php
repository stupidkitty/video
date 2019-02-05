<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

use RS\Component\Core\Widget\SettingsMenu;

$this->title = 'Настройки';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">

	<div class="col-md-3">
		<?php echo SettingsMenu::widget() ?>
	</div>

	<div class="col-md-9">
		<?php $activeForm = ActiveForm::begin([
			'action' => Url::to(['index']),
		]); ?>
			<div class="nav-tabs-custom">
				<ul class="nav nav-tabs">
					<li class="active"><a href="#rotation" data-toggle="tab" aria-expanded="true">Ротация</a></li>
					<li class=""><a href="#related" data-toggle="tab" aria-expanded="false">Похожие ролики</a></li>
					<li class=""><a href="#auto-posting" data-toggle="tab" aria-expanded="false">Автопостинг</a></li>
				</ul>

				<div class="tab-content">
					<div class="tab-pane active" id="rotation">
					    <?= $activeForm->field($form, 'items_per_page')
					    		->textInput(['maxlength' => true])
					    		->label('Количество тумб на страницу')
					    ?>

					    <?= $activeForm->field($form, 'pagination_buttons_count')
					    		->textInput(['maxlength' => true])
					    		->label('Количество кнопок в пагинации')
					    ?>

					    <?= $activeForm->field($form, 'recalculate_ctr_period')
					    		->textInput(['maxlength' => true])
					    		->label('Период пересчета CTR (в показах)')
					    		->hint('Параметр рассчитывает CTR за последние N показов. Расчет производится плавно и поделен на 5 этапов.')
					    ?>

					    <?= $activeForm->field($form, 'test_item_period')
					    		->textInput(['maxlength' => true])
					    		->label('Тестовый период тумбы (в показах)')
					    		->hint('Во время тестового периода тумба будет показываться в тестовой зоне на странице.
					    				По завершению теста тумба будет показываться на общих основаниях с учетом текущего CTR')
					    ?>

					    <?= $activeForm->field($form, 'test_items_start')
					    		->textInput(['maxlength' => true])
					    		->label('После какой тумбы будет тестовая зона')
					    ?>

					    <?= $activeForm->field($form, 'test_items_percent')
					    		->textInput(['maxlength' => true])
					    		->label('Процент тестовых тумб на странице')
					    		->hint('Рассчет ведется от общего числа тумб')
					    ?>

                        <?= $activeForm->field($form, 'internal_register_activity')
					    		->checkbox(['label' => 'Скрытая ротация'])
					    		->hint('Учет кликов и показов будет производится во время генерации страницы.')
					    ?>

					</div>

					<div class="tab-pane" id="related">

                        <div class="form-group">
							<label><?= Html::activeCheckbox($form, 'related_enable', ['label' => false]) ?> <span>Включить отображение виджета "Похожие видео"</span></label>
							<div class="help-block"></div>
						</div>

					    <?= $activeForm->field($form, 'related_number')
					    		->textInput(['maxlength' => true])
					    		->label('Сколько похожих роликов искать')
					    ?>

						<div class="form-group">
							<label><?= Html::activeCheckbox($form, 'related_allow_description', ['label' => false]) ?> <span>Учитывать описание</span></label>
							<div class="help-block"></div>
						</div>

						<div class="form-group">
							<label><?= Html::activeCheckbox($form, 'related_allow_categories', ['label' => false]) ?> <span>Учитывать категории</span></label>
							<div class="help-block"></div>
						</div>

					</div>

                    <div class="tab-pane" id="auto-posting">

                        <?= $activeForm->field($form, 'autoposting_fixed_interval')
					    		->textInput()
					    		->label('Фиксированный интервал')
                                ->hint('<b>В секундах.</b> Через какое время генерировать дату следующего поста. В сутках 86400 секунд')
					    ?>

                        <?= $activeForm->field($form, 'autoposting_spread_interval')
					    		->textInput()
					    		->label('Случайный разброс')
                                ->hint('<b>В секундах.</b> Данное время делится пополам формируя положительные и отрицательные границы случайной генерации времени для каждой временной метки фиксированного интервала. В часе 3600 секунд.')
					    ?>

					</div>
				</div>

				<div class="box-footer clearfix">
				    <div class="form-group">
						<?= Html::submitButton('Сохранить', ['class' => 'btn btn-success']) ?>
					</div>
				</div>
			</div>
		<?php ActiveForm::end() ?>

	</div>
</div>
