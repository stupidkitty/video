<?php

use yii\widgets\ActiveForm;

?>

    <?php $activeForm = ActiveForm::begin([
        'id' => 'crop-form',
    ]) ?>

    <?= $activeForm->field($form, 'name')->textInput([
            'options' => [
                'pattern' => '[a-z0-9-]',
            ]
        ])->hint('
            Название формата. Также название директории, куда будут складываться изображения данного формата.
            Рекомендуется использовать только латинские буквы в нижнем регистре и цифры. Без пробелов.
        ')
    ?>

    <?= $activeForm->field($form, 'comment')->textInput()->hint('Пояснительный комментарий к формату') ?>

    <?= $activeForm->field($form, 'command')->textarea(['rows' => 3])->hint('Строчка с опциями и фильтрами для imagick\'a. <span class="text-red">Изменения коснуться только новосозданных файлов</span>.') ?>

    <?php ActiveForm::end() ?>
