<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;
use yii\grid\CheckboxColumn;
use yii\widgets\LinkPager;

/* @var $this yii\web\View */
/* @var $dataProvider yii\data\ActiveDataProvider */
$dataProvider->prepare(true);
$page = $dataProvider->getPagination()->getPage() + 1;
$pageTitleSuffix = ($page > 1) ? Yii::t('app', 'page_suffix', ['page' => $page]) : '';

$this->title = 'Видео';
$this->params['subtitle'] = 'Список';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
	<div class="col-md-12">

	    <?= $this->render('_filter', [
	        'filterForm' => $filterForm,
	    ]) ?>

		<div class="box box-default">
			<div class="box-header with-border">
				<i class="fa fa-list"></i><h3 class="box-title">Видео ролики <?= $pageTitleSuffix ?></h3>
				<div class="box-tools pull-right">
					<div class="btn-group">
						<?= Html::a('<i class="glyphicon glyphicon-import text-light-violet"></i>', ['import/videos'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт видео']) ?>
					</div>
				</div>
            </div>

            <div class="box-body pad">

				<div class="table-actions-bar">
					<div class="btn-group" style="margin: 5px 0;">
						<button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">Действия с выбранными <span class="caret"></span></button>
			            <ul class="dropdown-menu" role="menu">
			                <li><a href="<?= Url::to(['batch-actions']) ?>" tabindex="-1" data-toggle="modal" data-target="#batch-actions-modal"><i class="fa fa-fw fa-wrench"></i><?= Yii::t('videos', 'Changes') ?></a></li>

			                <li class="divider"></li>
							<li><a href="#" class="batch-delete-videos text-red" tabindex="-1" data-url="<?= Url::to(['batch-delete']) ?>"><i class="fa fa-fw fa-trash-o"></i>Delete</a></li>
						</ul>
			        </div>

					<?= LinkPager::widget([
					    'pagination' => $dataProvider->pagination,
				    	'lastPageLabel' => '>>',
				    	'firstPageLabel' => '<<',
				    	'maxButtonCount' => 7,
					    'options' => [
					    	'class' => 'pagination pagination-sm no-margin pull-right',
					    ],
					]) ?>
				</div>

			    <?= GridView::widget([
			        'dataProvider' => $dataProvider,
			        'layout'=>"{summary}\n{items}",
			        'id' => 'list-videos',
			        'options' => [
			        	'class' => 'grid-view table-responsive',
			        ],
			        'columns' => [
			        	[
			        		'class' => CheckboxColumn::class,
			        		'options' => [
			        			'style' => 'width:30px',
			        		],
			        	],
			            [
			            	'attribute' => 'video_id',
			            	'label' => Yii::t('app', 'id'),
			            	'value' => function ($data) {
			            		return $data->video_id;
			            	},
			        		'options' => [
			        			'style' => 'width:70px',
			        		],
			            ],
			            //'image_id',
			            //'user_id',
			            //'slug',
			            [
			            	'attribute' => 'title',
			            	'label' => Yii::t('app', 'title'),
			            	'value' => function ($video) {
			            		return Html::a($video->title, ['update', 'id' => $video->getId()]);
			            	},
			            	'format' => 'html',
			            ],
			            // 'description:ntext',
			            // 'short_description',
			            // 'orientation',
			            // 'duration',
			            // 'video_url:url',
			            // 'embed:ntext',
			            // 'on_index',
			            // 'likes',
			            // 'dislikes',
			            // 'comments_num',
			            // 'views',
			            // 'status',
			            [
			            	'attribute' => 'published_at',
			            	'label' => Yii::t('app', 'published_at'),
			            	'format' => 'html',
			            	'value' => function ($data) {
			            		return Yii::$app->formatter->asDateTime($data->published_at);
			            	},
			        		'options' => [
			        			'style' => 'width:145px',
			        		],
			            ],
			            // 'created_at',
			            // 'updated_at',

			            ['class' => ActionColumn::class],
			        ],
			    ]); ?>

				<div class="table-actions-bar">
					<div class="btn-group dropup" style="margin: 5px 0;">
						<button class="btn btn-warning btn-sm dropdown-toggle" type="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false"> Действия с выбранными <span class="caret"></span></button>
			            <ul class="dropdown-menu" role="menu">
			                <li><a href="<?= Url::to(['batch-actions']) ?>" tabindex="-1" data-toggle="modal" data-target="#batch-actions-modal"><i class="fa fa-fw fa-wrench"></i><?= Yii::t('videos', 'Changes') ?></a></li>

			                <li class="divider"></li>
							<li><a href="#" class="batch-delete-videos text-red" tabindex="-1" data-url="<?= Url::to(['batch-delete']) ?>"><i class="fa fa-fw fa-trash-o"></i>Delete</a></li>
						</ul>
			        </div>

					<?= LinkPager::widget([
					    'pagination' => $dataProvider->pagination,
				    	'lastPageLabel' => '>>',
				    	'firstPageLabel' => '<<',
				    	'maxButtonCount' => 7,
					    'options' => [
					    	'class' => 'pagination pagination-sm no-margin pull-right',
					    ],
					]) ?>
				</div>

			</div>

		</div>

	</div>
</div>

<!-- Modal -->
<div class="modal fade" id="batch-actions-modal" role="dialog" aria-labelledby="batch_actions_title">
	<div class="modal-dialog modal-lg" role="document">
		<div class="modal-content"></div>
	</div>
</div>

<?php
$this->registerCssFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/css/select2.min.css');
$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.5/js/select2.full.min.js', [
	'depends' => [yii\web\JqueryAsset::class],
]);


$js = <<< 'JAVASCRIPT'
	$('.batch-delete-videos').click(function(event) {
		event.preventDefault();
		var actionUrl = $(this).data('url');
		var keys = $('#list-videos').yiiGridView('getSelectedRows');

		if (keys.length == 0) {
			alert('Нужно выбрать хотябы 1 элемент');
			return;
		}

		if (confirm('Уверены, что хотите удалить выбранные тумбы?')) {
			//preloader.show();
			$.post(actionUrl, {'videos_ids[]':keys}, function( data ) {
				if (data.error !== undefined) {
					//preloader.hide();
					toastr.error(data.error.message);
				} else {
					window.location.reload();
				}
			}, 'json');
		}
	});

	$(document).on('hidden.bs.modal', function (e) {
	    var target = $(e.target);
	    target.removeData('bs.modal')
	    .find('.modal-content').html('');
	});

	$('.category-select').select2({
		minimumResultsForSearch: -1,
		placeholder: 'Выбор категорий',
		allowClear: true,
		tokenSeparators: [',']
	});

	$('.category-select').on('select2:select', function (event) {
	  var element = event.params.data.element;
	  var $element = $(element);

	  $element.detach();
	  $(this).append($element);
	  $(this).trigger('change');
	});

	$('.user-search').select2();
JAVASCRIPT;

$this->registerJS($js, \yii\web\View::POS_END);
?>
