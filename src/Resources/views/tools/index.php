<?php

use yii\helpers\Url;
use yii\helpers\Html;
use yii\grid\GridView;

/* @var $this yii\web\View */
/* @var $model yii\base\Model */

$this->title = 'Разное';
$this->params['subtitle'] = 'Видео';
$this->params['breadcrumbs'][] = $this->title;

?>

<div class="row">
    <div class="col-md-12">

        <div class="box box-default">
            <div class="box-header with-border">
                <i class="fa fa-wrench"></i><h3 class="box-title">Разное</h3>
            </div>

            <div class="box-body pad">

                <table class="table">
                    <tr>
                        <td>
                            <h4>Пересчитать видео в категориях</h4>
                            <div class="text-muted">В категориях будет произведен подсчет только активных видео.</div>
                        </td>
                        <td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-info" id="recalculate_categories_videos" data-action="<?= Url::to(['recalculate-categories-videos'])?>" >Пересчитать видео</button></td>
                    </tr>

                    <tr>
                        <td>
                            <h4>Установить тумбы для категорий</h4>
                            <div class="text-muted">Тумбы установятся от первых видео на странице категории</div>
                        </td>
                        <td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-success" id="set_categories_thumbs" data-action="<?= Url::to(['set-categories-thumbs']) ?>">Установить тумбы</button></td>
                    </tr>

                    <tr>
                        <td>
                            <h4>Случайные даты публикации видео</h4>
                            <div class="text-muted">Задать случайную дату в промежутке за последний год по текущую дату.</div>
                        </td>
                        <td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-info" id="random_date" data-action="<?= Url::to(['random-date']) ?>">Задать дату</button></td>
                    </tr>

                    <tr>
                        <td>
                            <h4>Обнуление статистики</h4>
                            <div class="text-muted">Обнулить полностью статистику кликов и показов тумб, категорий. А также просмотры видео, лайки и дизлайки.</div>
                        </td>
                        <td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-warning" id="clear_stats" data-action="<?= Url::to(['clear-stats']) ?>">Обнулить статистику</button></td>
                    </tr>

                    <tr>
                        <td>
                            <h4>Очистить "похожие" видео</h4>
                            <div class="text-muted">"Похожие" ролики будут полностью удалены из базы.</div>
                        </td>
                        <td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-warning" id="clear_related" data-action="<?= Url::to(['clear-related']) ?>">Очистить "похожие"</button></td>
                    </tr>

                    <tr>
                        <td>
                            <h4>Очистить базу видео</h4>
                            <div class="text-muted">Полностью удалить видео, скриншоты, статистику по тумбам.</div>
                        </td>
                        <td style="vertical-align:middle;"><button type="button" class="btn btn-block btn-danger" id="clear_videos" data-action="<?= Url::to(['clear-videos']) ?>">Удалить все видео</button></td>
                    </tr>
                </table>

            </div>
        </div>

    </div>
</div>

<?php

$js = <<< 'JS'
    (function() {
        $('#recalculate_categories_videos').click(function(event) {
            event.preventDefault();
            var actionUrl = $(this).data('action');
            var bttn = $(this);

            bttn.prop('disabled', true);

            $.post(actionUrl, function( data ) {
                if (data.error !== undefined) {
                    toastr.error(data.error.message, 'Error');
                } else {
                    toastr.success(data.message, 'Success');
                }
            }, 'json')
            .done(function() {
                bttn.prop('disabled', false);
            });
        });

        $('#set_categories_thumbs').click(function(event) {
            event.preventDefault();
            var bttn = $(this);
            var actionUrl = $(this).data('action');

            bttn.prop('disabled', true);

            $.post(actionUrl, function( data ) {
                if (data.error !== undefined) {
                    toastr.error(data.error.message, 'Error');
                } else {
                    toastr.success(data.message, 'Success');
                }
            }, 'json')
            .done(function() {
                bttn.prop('disabled', false);
            });
        });

        $('#random_date').click(function(event) {
            event.preventDefault();
            var bttn = $(this);
            var actionUrl = $(this).data('action');

            if (confirm('Задать случайную дату у всех видео роликов??')) {
                bttn.prop('disabled', true);

                $.post(actionUrl, function( data ) {
                    if (data.error !== undefined) {
                        toastr.error(data.error.message, 'Error');
                    } else {
                        toastr.success(data.message, 'Success');
                    }
                }, 'json')
                .done(function() {
                    bttn.prop('disabled', false);
                });
            }
        });

        $('#clear_stats').click(function(event) {
            event.preventDefault();
            var actionUrl = $(this).data('action');

            if (confirm('Обнулить статистику тумб, категорий и видео?')) {
                $.post(actionUrl, function( data ) {
                    if (data.error !== undefined) {
                        toastr.error(data.error.message, 'Error');
                    } else {
                        toastr.success(data.message, 'Success');
                    }
                }, 'json');
            }
        });

        $('#clear_related').click(function(event) {
            event.preventDefault();
            var actionUrl = $(this).data('action');

            if (confirm('Очистить "похожие видео"?')) {
                $.post(actionUrl, function( data ) {
                    if (data.error !== undefined) {
                        toastr.error(data.error.message, 'Error');
                    } else {
                        toastr.success(data.message, 'Success');
                    }
                }, 'json');
            }
        });

        $('#clear_videos').click(function(event) {
            event.preventDefault();
            var actionUrl = $(this).data('action');
            var confirmed = prompt('Для полного удаления видео напишите слово DELETE', '');

            if (confirmed != null && confirmed === 'DELETE') {
                bttn.prop('disabled', true);

                $.post(actionUrl, function( data ) {
                    if (data.error !== undefined) {
                        toastr.error(data.error.message, 'Error');
                    } else {
                        toastr.success(data.message, 'Success');
                    }
                }, 'json')
                .done(function() {
                    bttn.prop('disabled', false);
                });
            }
        });
    })();
JS;

$this->registerJS($js, \yii\web\View::POS_END);

?>
