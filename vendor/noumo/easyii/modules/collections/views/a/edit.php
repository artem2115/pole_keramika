<?php
// vendor/noumo/easyii/modules/collections/views/a/edit.php

$this->title = $model->title;
?>
<?= $this->render('_menu') ?>
<?= $this->render('_submenu', ['model' => $model]) ?>
<?= $this->render('_form', ['model' => $model]) ?>