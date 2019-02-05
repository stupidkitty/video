<?php

?>

<?= $activeForm->field($form, 'title')->textInput(['maxlength' => true]) ?>

<?= $activeForm->field($form, 'slug')->textInput(['maxlength' => true])
    ->hint('Оставить пустым для автоматичекой генерации из названия')
?>

<?= $activeForm->field($form, 'meta_title')->textInput(['maxlength' => true]) ?>

<?= $activeForm->field($form, 'meta_description')->textInput(['maxlength' => true]) ?>

<?= $activeForm->field($form, 'h1')->textInput(['maxlength' => true]) ?>

<?= $activeForm->field($form, 'description')->textarea(['rows' => 5]) ?>

<?= $activeForm->field($form, 'seotext')->textarea(['rows' => 5]) ?>

<?= $activeForm->field($form, 'param1')->textarea(['rows' => 5]) ?>

<?= $activeForm->field($form, 'param2')->textarea(['rows' => 5]) ?>

<?= $activeForm->field($form, 'param3')->textarea(['rows' => 5]) ?>

<?= $activeForm->field($form, 'on_index')->checkbox() ?>

<?= $activeForm->field($form, 'enabled')->checkbox() ?>
