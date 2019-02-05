<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\widgets\DetailView;

/* @var $this yii\web\View */
/* @var $category backend\models\videos\VideosCategories */

$this->title = Yii::t('videos', 'videos');
$this->params['subtitle'] = Yii::t('videos', 'info');

$this->params['breadcrumbs'][] = ['label' => 'Категории', 'url' => ['create']];
$this->params['breadcrumbs'][] = Yii::t('videos', 'info');

?>

<div class="row">

	<div class="col-md-4">
		<?= $this->render('_left_sidebar', [
			'categories' => $categories,
			'active_id' => isset($category)? $category->getId() : 0,
		]) ?>
	</div>

	<div class="col-md-8">
		<div class="box box-info">
			<div class="box-header with-border">
				<i class="fa fa-info-circle"></i><h3 class="box-title">Информация: <?= $category->title ?></h3>
				<div class="box-tools pull-right">
					<div class="btn-group">
						<?= Html::a('<i class="glyphicon glyphicon-import" style="color:#ad00ff;"></i> ' . Yii::t('videos', 'import'), ['import/categories'], ['class' => 'btn btn-default btn-sm', 'title' => 'Импорт категорий']) ?>
						<?= Html::a('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['create'], ['class' => 'btn btn-default btn-sm', 'title' => 'Добавить категорию']) ?>
						<?= Html::a('<i class="fa fa-fw fa-edit text-blue"></i>' . Yii::t('videos', 'edit'), ['update', 'id' => $category->getId()], ['class' => 'btn btn-default btn-sm', 'title' => 'Редактировать категории']) ?>
						<?= Html::a('<i class="fa fa-fw fa-trash-o text-red"></i>' . Yii::t('videos', 'delete'), ['delete', 'id' => $category->getId()], [
				            'class' => 'btn btn-default btn-sm',
				            'title' => 'Удалить категорию',
				            'data' => [
				                'confirm' => 'Действительно хотите удалить эту категорию?',
				                'method' => 'post',
				            ],
				        ]) ?>
					</div>
				</div>
            </div>

            <div class="box-body pad">

			    <?= DetailView::widget([
			        'model' => $category,
			        'attributes' => [
			            'category_id',
                        'title',
			            'slug',
			            'h1',
			            'image:image',
			            'meta_title',
			            'meta_description',
			            'description:ntext',
			            'seotext:ntext',
			            'param1:ntext',
			            'param2:ntext',
			            'param3:ntext',
			            'videos_num',
			            'on_index',
			            'enabled',
			            'last_period_clicks',
			            'position',
			            'created_at',
			            'updated_at',
			        ],
			    ]) ?>

			</div>

		</div>

        <div class="box box-default">
			<div class="box-header with-border">
				<i class="fa fa-area-chart"></i><h3 class="box-title">Активность в категории за последниe 30 дней</h3>
                <div class="box-tools pull-right">
                    <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
                    <button type="button" class="btn btn-box-tool" data-widget="remove"><i class="fa fa-times"></i></button>
                 </div>
            </div>

            <div class="box-body pad">
                <div style="position:relative">
                    <canvas id="line-chart" width="320" height="160"></canvas>
			    </div>
			</div>

		</div>

	</div>
</div>

<?php

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.2/Chart.min.js', ['depends' => [\RS\Module\AdminModule\Asset\AdminLteAsset::class]]);
$this->registerJsVar('chartLabels', array_column($stats, 'date'));
$this->registerJsVar('chartPoints', array_column($stats, 'clicks'));

$script = <<< 'JAVASCRIPT'
    var chart = document.querySelector('#line-chart').getContext('2d');

    gradient = chart.createLinearGradient(0, 0, 0, 450);
    gradient.addColorStop(0, 'rgba(210, 0,0, 1)');
    gradient.addColorStop(1, 'rgba(120, 0, 0, 1)');
    //gradient.addColorStop(1, 'rgba(255, 0, 0, 0)');

    var options = {
    	responsive: true,
    	maintainAspectRatio: true,
    	animation: {
    		easing: 'easeInOutQuad',
    		duration: 520
    	},
    	scales: {
    		xAxes: [{
    			gridLines: {
    				color: 'rgba(200, 200, 200, 0.05)',
    				lineWidth: 1
    			}
    		}],
    		yAxes: [{
    			gridLines: {
    				color: 'rgba(200, 200, 200, 0.08)',
    				lineWidth: 1
    			}
    		}]
    	},
    	elements: {
    		line: {
    			tension: 0.4
    		}
    	},
    	legend: {
    		display: false
    	},
    	point: {
    		backgroundColor: 'white'
    	},
    	tooltips: {
    		titleFontFamily: 'Open Sans',
    		backgroundColor: 'rgba(0,0,0,0.3)',
    		titleFontColor: 'red',
    		caretSize: 5,
    		cornerRadius: 2,
    		xPadding: 10,
    		yPadding: 10
    	}
    };

    var data = {
        labels : chartLabels,
        datasets: [{
            backgroundColor: gradient,
			pointBackgroundColor: 'white',
			borderWidth: 1,
			borderColor: '#911215',
            data : chartPoints
        }]
    };

    var myChart = new Chart(chart, {
        type: 'line',
        data: data,
        options: options
    });
JAVASCRIPT;


$this->registerJS($script);
