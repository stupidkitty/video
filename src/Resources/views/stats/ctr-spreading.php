<?php

use yii\web\View;

$this->title = 'Статистика';
$this->params['subtitle'] = 'Видео';

$this->params['breadcrumbs'][] = ['label' => $this->title, 'url' => ['index']];
$this->params['breadcrumbs'][] = $this->params['subtitle'];

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title">График распределения цтр</h3>
        <div class="box-tools pull-right">
            <button type="button" class="btn btn-box-tool" data-widget="collapse"><i class="fa fa-minus"></i></button>
        </div>
    </div>

    <div class="box-body">
        <canvas id="ctr-chart" width="1200" height="400"></canvas>
    </div>
</div>

<?php $this->registerJsVar('ctrLabels', $labels) ?>
<?php $this->registerJsVar('ctrValues', $values) ?>

<?php

$js = <<< 'Javascript'
    var ctx = document.querySelector('#ctr-chart');

    var myChart = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ctrLabels,
            datasets: [{
                label: 'Num of Videos',
                data: ctrValues,
                backgroundColor: 'rgba(255, 99, 132, 0.6)',
                //borderColor: 'rgba(255, 99, 132, 1)',
                //borderWidth: 1
            }]
        },
        options: {
            scales: {
                yAxes: [{
                    ticks: {
                        beginAtZero:true
                    }
                }]
            }
        }
    });
Javascript;

$this->registerJsFile('https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.3/Chart.min.js', ['position' => View::POS_END]);
$this->registerJs($js);
