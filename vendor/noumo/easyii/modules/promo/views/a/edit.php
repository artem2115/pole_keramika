<?php
$this->title = 'Редактировать промокод: ' . $model->code;
?>
<?= $this->render('_menu') ?>
<?= $this->render('_form', ['model' => $model]) ?>