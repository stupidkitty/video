<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\ActiveForm;

?>

<div class="modal-header">
	<button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">×</span></button>
    <h4 class="modal-title"><?= Yii::t('videos', 'Batch actions') ?></h4>
</div>

<div class="modal-body row">
	<div class="col-md-12">
		<?= Html::beginForm(['main/batch-actions'], 'post', ['id' => 'batch-actions-form', 'class' => 'form-horizontal']) ?>
			<div class="form-group">
				<label class="col-md-3 control-label"><?= Yii::t('videos', 'User') ?> <?= Html::checkbox('isChangeUser', false) ?></label>
				<div class="col-md-9">
					<?= Html::dropDownList(
							'user_id',
							null,
							$form->getUsers(),
							[
								'prompt' => '-- Select user --',
								'id' => 'select-user',
								'class' => 'form-control user-search',
								'style' => 'width:initial;',
							]
						)
					?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?= Yii::t('videos', 'Status') ?> <?= Html::checkbox('isChangeStatus', false) ?></label>
				<div class="col-md-9">
					<?= Html::dropDownList(
							'status',
							null,
							$form->getStatuses(),
							[
								'prompt' => '-- Select status --',
								'id' => 'select-status',
								'class' => 'form-control',
								'style' => 'width:initial;',
							]
						)
					?>
				</div>
			</div>

            <div class="form-group">
				<label class="col-md-3 control-label"><?= Yii::t('videos', 'orientation') ?> <?= Html::checkbox('isChangeOrientation', false) ?></label>
				<div class="col-md-9">
					<?= Html::dropDownList(
							'orientation',
							null,
                            [
                                1 => 'Straight',
                                2 => 'Lesbian',
                                3 => 'Shemale',
                                4 => 'Gay',
                            ],
							[
								'prompt' => '-- Select orientation --',
								'id' => 'select-orientation',
								'class' => 'form-control',
								'style' => 'width:initial;',
							]
						)
					?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?= Yii::t('videos', 'Add categories') ?> <?= Html::checkbox('isAddCategories', false) ?></label>
				<div class="col-md-9">
					<?= Html::dropDownList(
							'add_categories_ids',
							null,
							$form->getCategories(),
							[
								'class' => 'form-control category-select',
								'multiple' => true,
							]
						)
					?>
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-3 control-label"><?= Yii::t('videos', 'Remove categories') ?> <?= Html::checkbox('isDeleteCategories', false) ?></label>
				<div class="col-md-9">
					<?= Html::dropDownList(
							'delete_categories_ids',
							null,
							$form->getCategories(),
							[
								'class' => 'form-control category-select',
								'multiple' => true,
							]
						)
					?>
				</div>
			</div>
		<?= Html::endForm() ?>
	</div>
</div>

<div class="modal-footer">
	<button type="button" class="btn btn-default pull-left" data-dismiss="modal"><i class="fa fa-fw fa-close text-red"></i><?= Yii::t('videos', 'Close') ?></button>
	<button type="button" class="btn btn-primary pull-right" id="submit-batch-actions"><i class="fa fa-fw fa-check"></i><?= Yii::t('videos', 'Save changes') ?></button>
</div>

<script>
	var batchActionsForm = $('#batch-actions-form');
	var categoriesSelect = batchActionsForm.find('.category-select');

	$('#submit-batch-actions').on('click', function(event) {
	    event.preventDefault();

		var actionUrl = batchActionsForm.attr('action');
		var formData = batchActionsForm.serializeArray();
		var keys = $('#list-videos').yiiGridView('getSelectedRows');

		if (keys.length == 0) {
			alert('Select the element');
			return;
		}

		for (var key in keys) {
		    if (keys.hasOwnProperty(key)) {
		        formData.push({name:'videos_ids[]', value:keys[key]});
		    }
		}

		$.post(actionUrl, formData, function( data ) {
			if (data.error !== undefined) {
				toastr.error(data.error.message);
			} else {
				window.location.reload();
			}
		}, 'json');
	});

	categoriesSelect.select2({
		minimumResultsForSearch: -1,
		placeholder: 'Choice categories',
		allowClear: true,
		tokenSeparators: [',']
	});

	categoriesSelect.on('select2:select', function (event) {
	  var element = event.params.data.element;
	  var $element = $(element);

	  $element.detach();
	  $(this).append($element);
	  $(this).trigger('change');
	});

	$('#select-user').select2();
</script>
