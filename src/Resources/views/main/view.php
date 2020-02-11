<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\helpers\ArrayHelper;
use yii\widgets\DetailView;

$this->title = Yii::t('videos', 'videos');
$this->params['subtitle'] = Yii::t('videos', 'info');

$this->params['breadcrumbs'][] = ['label' => Yii::t('videos', 'videos'), 'url' => ['index']];
$this->params['breadcrumbs'][] = $video->title;

?>

<div class="box box-default">
    <div class="box-header with-border">
        <h3 class="box-title"><?= Html::encode($video->title) ?></h3>

        <div class="btn-group pull-right">
            <?= Html::a('<i class="fa fa-fw fa-plus text-green"></i>' . Yii::t('videos', 'add'), ['create'], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-edit text-blue"></i>' . Yii::t('videos', 'edit'), ['update', 'id' => $video->getId()], ['class' => 'btn btn-default btn-sm']) ?>
            <?= Html::a('<i class="fa fa-fw fa-trash text-red"></i>' . Yii::t('videos', 'delete'), ['delete', 'id' => $video->getId()], [
                'class' => 'btn btn-default btn-sm',
                'data' => [
                    'confirm' => Yii::t('videos', 'Are you sure you want to delete this item?'),
                    'method' => 'post',
                ],
            ]) ?>
        </div>
    </div>

    <div class="box-body pad">

        <div class="row">
            <div class="col-md-4">
                <div class="player">
                    <iframe width="342" height="180" src="<?= "{$this->params['video.embed.base_url']}{$video->embed}" ?>" frameborder="0" allowfullscreen="" scrolling="no"></iframe>
                </div>
                <div style="margin-top:15px;">
                    <video controls poster="<?= $video->poster->filepath ?>">
                        <source src="<?= "{$this->params['video.preview.base_url']}{$video->video_preview}" ?>" type="video/mp4">
                        Your browser doesn't support HTML5 video tag.
                    </video>
                </div>
                <div style="margin-top:15px;">
                    <?= Html::img($video->poster->filepath) ?>
                </div>
            </div>

            <div class="col-md-8">
                <?= DetailView::widget([
                    'model' => $video,
                    'template' => "<tr><th width=\"150\">{label}</th><td>{value}</td></tr>",
                    'attributes' => [
                        'video_id',
                        [
                            'attribute' => 'user_id',
                            'value' => function ($video) {
                                return $video->user->username;
                            },
                        ],
                        'title',
                        'slug',
                        'description:ntext',
                        'short_description',
                        [
                            'label' => 'Categories',
                            'value' => function ($video) {
                                return implode(', ', ArrayHelper::getColumn($video->categories, 'title'));
                            },
                        ],
                        'published_at:datetime',
                        [
                            'attribute' => 'duration',
                            'value' => function ($video) {
                                return Yii::$app->formatter->asDuration($video->duration);
                            },
                        ],
                        [
                            'attribute' => 'orientation',
                            'value' => function ($video) {
                                $array = [
                                    1 => 'Straight',
                                    2 => 'Lesbian',
                                    3 => 'Shemale',
                                    4 => 'Gay',
                                ];

                                return isset($array[$video->orientation]) ? $array[$video->orientation] : '<span class="not-set">(not set)</span>';
                            },
                            'format' => 'html',
                        ],
                        'video_url:url',
                        'source_url:url',
                        'embed:ntext',
                        'video_preview',
                        'likes',
                        'dislikes',
                        'comments_num',
                        [
                            'attribute' => 'views',
                            'value' => function ($video) {
                                return Yii::$app->formatter->asInteger($video->views);
                            },
                        ],
                        [
                            'attribute' => 'status',
                            'value' => function ($video) use ($statusLabel) {
                                return isset($statusLabel[$video->status]) ? $statusLabel[$video->status] : '<span class="not-set">(unknown)</span>';
                            },
                            'format' => 'html',
                        ],
                        'is_hd',
                        'on_index',
                        'noindex',
                        'nofollow',
                        'created_at:datetime',
                        'updated_at:datetime',
                    ],
                ]) ?>
            </div>
        </div>

    </div>
</div>

<?php if (!empty($categoriesRotationStats)): ?>
    <div class="box box-default">
        <div class="box-header with-border">
            <h3 class="box-title">Статистика ротации по категориям</h3>
        </div>

        <div class="box-body pad">
            <table class="table">
                <thead>
                    <tr>
                        <th width="150">Category</th>
                        <th>Ctr</th>
                        <th>Total clicks</th>
                        <th>Total shows</th>
                        <th>Is tested</th>
                        <th>Iteration num</th>
                        <th>Current clicks</th>
                        <th>Current shows</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($categoriesRotationStats as $row): ?>
                        <tr>
                            <td><?= $row->category->title ?></td>
                            <td><?= $row->ctr ? "<b>{$row->ctr}</b>" : 0 ?></td>
                            <td><?= Yii::$app->formatter->asInteger($row->total_clicks) ?></td>
                            <td><?= Yii::$app->formatter->asInteger($row->total_shows) ?></td>
                            <td><?= $row->is_tested ? 'Yes' : 'No' ?></td>
                            <td><?= Yii::$app->formatter->asInteger($row->current_index) ?></td>
                            <td><?= Yii::$app->formatter->asInteger($row->current_clicks) ?></td>
                            <td><?= Yii::$app->formatter->asInteger($row->current_shows) ?></td>
                        </tr>
                    <?php endforeach ?>
                </tbody>
            </table>
        </div>
    </div>
<?php endif ?>
