<?php

use yii\helpers\Html;
use yii\grid\GridView;
use yii\grid\ActionColumn;

$this->title = Yii::t('videos', 'crop_profiles');
$this->params['subtitle'] = Yii::t('videos', 'overview');

?>

<div class="box box-default">
	<div class="box-header with-border">
        <h3 class="box-title"></h3>
		<div class="box-tools pull-right">
			<div class="btn-group">
                <?= Html::a('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['create'], ['class' => 'btn btn-default btn-sm']) ?>
            </div>
		</div>
    </div>

    <div class="box-body pad">

	    <?= GridView::widget([
	        'dataProvider' => $dataProvider,
	        'id' => 'list-galleries',
	        'options' => [
	        	'class' => 'grid-view table-responsive',
	        ],
	        'columns' => [
	            [
	            	'attribute' => 'crop_id',
	            	'label' => Yii::t('videos', 'id'),
	            	'value' => function ($crop) {
	            		return $crop->getId();
	            	},
	        		'options' => [
	        			'style' => 'width:70px',
	        		],
	            ],
	            [
	            	'attribute' => 'name',
	            	'label' => Yii::t('videos', 'name'),
	            	'value' => function ($crop) {
	            		return Html::a($crop->getName(), ['update', 'id' => $crop->getId()]);
	            	},
	            	'format' => 'html',
	            ],
	            'comment:ntext',
	            [
	            	'attribute' => 'created_at',
	            	'label' => Yii::t('videos', 'created_at'),
	            	'format' => 'datetime',
	        		'options' => [
	        			'style' => 'width:145px',
	        		],
	            ],
	            ['class' => ActionColumn::class],
	        ],
	    ]); ?>

	</div>
</div>
