<?php

use \SK\VideoModule\Statistic\Report\RotationStatisticReport;
use SK\VideoModule\Statistic\Report\VideoStatisticReport;
use yii\helpers\Html;

/**
 * @var RotationStatisticReport $report
 * @var VideoStatisticReport $videoReport
 */

$this->title = 'Статистика';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['subtitle'];

$formatter = Yii::$app->formatter;

$currentTimestamp = \time();
$lastPublishedTimestamp = $videoReport->getLastPublicationDate()->getTimestamp();
$maxInterval = 86400 * 2;

$isLastPublicationDateOverdue = false;

if (($currentTimestamp - $maxInterval) > $lastPublishedTimestamp) {
  $isLastPublicationDateOverdue = true;
}

?>

<div class="row">
    <div class="col-md-6">

        <div class="box box-default">
        	<div class="box-header with-border">
        		<h3 class="box-title">Статистика ротации</h3>
            </div>

            <div class="box-body pad">
            	Всего тумб: <b><?= $report->getTotalThumbs() ?></b><br>
            	Тестирумые тумбы: <b><?= $report->getTestThumbs() ?></b><br>
            	Завершившие тест: <b><?= $report->getTestedThumbs() ?></b><br>
            	Нулевой цтр у прошедших тест: <b><?= $report->getTestedZeroCtrThumbs() ?></b>
                <?= Html::a(
                        '<i class="glyphicon glyphicon-repeat text-muted"></i> Перезапустить',
                        ['ajax/restart-zero-ctr'],
                        [
                            'id' => 'restart-zero-ctr',
                            'style' => [
                                'display' => 'inline-block',
                                'margin-left' => '10px',
                            ],
                        ]
                    );
                ?>

        		<div class="progress-group" style="margin-top:15px;">
        			<span class="progress-text">Прогресс тестирования</span>
        			<span class="progress-number"><b><?= $report->getTestedThumbs() ?></b> / <?= $report->getTotalThumbs() ?> (<?= $report->getTotalTestPercent() ?>%)</span>

        			<div class="progress">
        				<div class="progress-bar progress-bar-aqua" style="width: <?= $report->getTotalTestPercent() ?>%"></div>
        			</div>
        		</div>

        	</div>

        	<div class="box-footer clearfix">
        	    <div class="form-group">
        			<?= Html::a('<i class="fa fa-fw fa-refresh"></i>Обновить', ['stats/index'], ['class' => 'btn btn-default']) ?>
        		</div>
        	</div>
        </div>
    </div>

    <div class="col-md-6">
        <div class="box box-default">
        	<div class="box-header with-border">
        		<h3 class="box-title">Статистика по видео</h3>
            </div>

            <div class="box-body pad">
                <div class="row">
                    <div class="col-md-4">
                    	Всего постов: <b><?= $videoReport->getTotalVideos() ?></b><br>
                        Дата крайней публикации: <span<?= $isLastPublicationDateOverdue ? ' class="not-set"' : ''?> style="white-space: nowrap">
                          <?= $videoReport->getLastPublicationDate() ? $formatter->asDatetime($videoReport->getLastPublicationDate()) : '(none)' ?>
                        </span>
                        <h4>По статусам</h4>
                        Активные: <b><?= $videoReport->getActiveVideos() ?></b><br>
                        Выключеные: <b><?= $videoReport->getDisabledVideos() ?></b><br>
                        На модерации: <b><?= $videoReport->getModerateVideos() ?></b><br>
                        На удаление: <b><?= $videoReport->getDeletedVideos() ?></b><br>
        	        </div>
                    <div class="col-md-4">
                        Всего категорий: <b><?= $videoReport->getTotalCategories() ?></b><br>
                        <h4>По статусам</h4>
                        Активные: <b><?= $videoReport->getEnabledCategories() ?></b><br>
                        Выключеные: <b><?= $videoReport->getDisabledCategories() ?></b><br>
        	        </div>
                    <div class="col-md-4">
                        Всего изображений: <b><?= $videoReport->getTotalImages() ?></b><br>
        	        </div>
        	    </div>
        	</div>


        	<div class="box-footer clearfix">
        	    <div class="form-group">
        			<?= Html::a('<i class="fa fa-fw fa-refresh"></i>Обновить', ['stats/index'], ['class' => 'btn btn-default']) ?>
        		</div>
        	</div>
        </div>
    </div>

</div>

<div class="box box-success">
    <div class="box-header with-border">
        <h3 class="box-title">Ротация по категориям</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-plus"></i></button>
        </div>
    </div>

    <div class="box-body">
        Показано результатов: <b><?= count($report->getCategoriesReports()) ?></b><br>

        <?php if ($report->hasCategoriesReports()): ?>
            <?php foreach($report->getCategoriesReports() as $categoryReport): ?>
                <div class="progress-group">
                    <span class="progress-text"><?= $categoryReport->getTitle() ?></span>
                    <span class="progress-number">
                        <?= $categoryReport->getTestedThumbs() ?> (отротировано)
                        /
                        <b><?= $categoryReport->getTotalThumbs() ?></b> (всего)
                        | В ротации: <?= $categoryReport->getTotalThumbs() - $categoryReport->getTestedThumbs() ?>
                    </span>

                    <div class="progress">
                        <div class="progress-bar progress-bar-blue" style="width: <?= $categoryReport->getTotalTestPercent() ?>%"></div>
                    </div>
                </div>
            <?php endforeach ?>
        <?php endif ?>
    </div>

</div>

<?php

$js = <<< 'Javascript'
    var ctrRestarter = document.querySelector('#restart-zero-ctr')

    ctrRestarter.addEventListener('click', function (e) {
        e.preventDefault()

        fetch(this.getAttribute('href'), {
            method: 'POST',
            body: '',
            credentials: 'same-origin'
        })
        .then(function(response) {
            if(!response.ok) {
                throw new Error('Network response was not ok.')
            }

            return response.json()
        })
        .then(function(response) {
            if (response.error !== undefined) {
                throw new Error(response.error.message)
            }

            toastr.success(response.message)
        })
        .catch(function(error) {
            toastr.error(error.message)
        })
    })

Javascript;

$this->registerJs($js);
