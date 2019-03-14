<?php

use yii\helpers\Url;
use yii\helpers\Html;

?>

<div class="box box-default">
    <div class="box-header with-border">
        <i class="fa fa-list"></i><h3 class="box-title">Категории</h3>
        <div class="box-tools pull-right">
            <div class="btn-group">
                Сортировка: <?= Html::dropDownList('sort_items', 'position', [
                    'key' => 'ID',
                    'position' => 'Ручная',
                    'title' => 'Название',
                    'clicks' => 'Клики',
                ], [
                    'id' => 'sort-items',
                    'class' => 'btn-default btn-sm',
                ]) ?>
            </div>
        </div>
    </div>

    <div class="box-body pad">
        <?php if (!empty($categories)): ?>
            <ul id="sortable" class="categories-list">
            <?php foreach ($categories as $category): ?>

                <li 
                  class="categories-list__item <?= ($category->category_id === $active_id)? 'active' : ''?> <?= (!$category->isEnabled()) ? 'bg-pink--horizontal-gradient' : '' ?>"
                  data-key="<?= $category->category_id ?>"
                  data-position="<?= $category->position ?>"
                  data-title="<?= $category->title ?>"
                  data-clicks="<?= $category->last_period_clicks ?>"
                >
                    <span class="categories-list__span categories-list__span--id"><?= $category->category_id ?>: </span>
                    
                    <?= Html::a($category->title, ['update', 'id' => $category->category_id], ['title' => 'Редактирование', 'class' => 'categories-list__a categories-list__a--title']) ?>
                    <?= (!$category->isEnabled()) ? "({$category->videos_num}, выключена)" : "({$category->videos_num})" ?>
                    
                    <ul class="categories-list__actions action-buttons pull-right">
                        <li class="action-buttons__item">
                            <?= Html::a(
                                '<span class="glyphicon glyphicon-info-sign"></span>',
                                ['view', 'id' => $category->category_id],
                                [
                                    'title' => 'Просмотр информации',
                                    'class' => 'action-buttons__a',
                                ]
                            ) ?>
                        </li>
                        <li class="action-buttons__item">
                            <?= Html::a(
                                '<span class="glyphicon glyphicon-trash text-red"></span>',
                                ['delete', 'id' => $category->category_id],
                                [
                                    'title' => 'Удалить',
                                    'class' => 'action-buttons__a',
                                    'aria-label' => 'Удалить',
                                    'data-confirm' => 'Вы уверены, что хотите удалить эту категорию?',
                                    'data-method' => 'post',
                                ]
                            ) ?>
                        </li>
                    </ul>
                </li>

            <?php endforeach ?>
            </ul>
        <?php else: ?>
            Нет категорий
        <?php endif ?>
    </div>

    <div class="box-footer clearfix">
        <div class="btn-group pull-right dropup">
            <button type="button" class="btn btn-default">Действия</button>
            
            <button type="button" class="btn btn-default dropdown-toggle" data-toggle="dropdown" aria-expanded="true">
                <span class="caret"></span>
                <span class="sr-only">Toggle Dropdown</span>
            </button>

            <ul class="dropdown-menu" role="menu">
                <li>
                    <?= Html::a(
                        '<i class="fa fa-sort-numeric-asc text-blue"></i> Сохранить порядок сортировки',
                        ['save-order'],
                        [
                            'id' => 'save-order',
                        ]
                    ) ?>
                </li>
                <li>
                    <?= Html::a(
                        '<i class="glyphicon glyphicon-export" style="color:#ff196a"></i> Экспорт категорий', 
                        ['export']
                    ) ?>
                </li>
                <li>
                    <?= Html::a(
                        '<i class="fa fa-refresh text-yellow"></i> Пересчитать видео', 
                        ['recalculate-videos'],
                        [
                            'id' => 'recalculate-videos',
                        ]
                    ) ?>
                </li>
            </ul>
        </div>
    </div>
</div>

<?php

$script = <<< 'JAVASCRIPT'
    $("#sortable").sortable({
      placeholder: 'categories-list__placeholder',
      cursor: 'move',
    });

    var saveOrderButton = document.querySelector('#save-order');
    var recalculateVideosLink = document.querySelector('#recalculate-videos');
    var categoryList = document.querySelector('#sortable');

    saveOrderButton.addEventListener('click', function (event) {
        event.stopPropagation();
        event.preventDefault();

        let sendUrl = saveOrderButton.getAttribute('href');
        let categoriesItems = categoryList.querySelectorAll('[data-key]');
        let formData = new FormData();

        if (!categoriesItems.length) {
            return;
        }

        for (let i = 0; i < categoriesItems.length; i++) {
            let category = categoriesItems[i];
            let id = parseInt(category.getAttribute('data-key'), 10);

            if (NaN === id) {
                continue;
            }

            formData.append('order[]', id);
        }

        fetch(sendUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }

            return response;
        })
        .then(response => response.json())
        .then((data) => {
            if (data.error !== undefined) {
                throw new Error(data.error.message);
            }

            toastr.success(data.message);
        })
        .catch(function(error) {
            toastr.error(error.message);
        });
    });

    recalculateVideosLink.addEventListener('click', function (event) {
        event.stopPropagation();
        event.preventDefault();

        let sendUrl = this.href;
        let formData = new FormData();

        fetch(sendUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin'
        })
        .then((response) => {
            if (!response.ok) {
                throw new Error(response.statusText);
            }

            return response;
        })
        .then(response => response.json())
        .then((data) => {
            if (data.error !== undefined) {
                throw new Error(data.error.message);
            }

            toastr.success(data.message);
        })
        .catch(function(error) {
            toastr.error(error.message);
        });
    });

    $('#sort-items').on('change', function (event) {
        if ($(this).val() === 'clicks') {
            $("#sortable .categories-list__item").sort(sort_desc).appendTo('#sortable');
        } else {
            $("#sortable .categories-list__item").sort(sort_asc).appendTo('#sortable');
        }


        function sort_asc(a, b) {
            return ($(b).data($('#sort-items').val())) < ($(a).data($('#sort-items').val())) ? 1 : -1;
        }

        function sort_desc(a, b) {
            return ($(b).data($('#sort-items').val())) > ($(a).data($('#sort-items').val())) ? 1 : -1;
        }
    });
JAVASCRIPT;

$this->registerJS($script);
