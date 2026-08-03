<?php
//vendor/noumo/easyii/modules/completed/views/a/edit.php
$this->title = $model->title;
?>
<?= $this->render('_menu') ?>

<?php if($this->context->module->settings['enablePhotos']) echo $this->render('_submenu', ['model' => $model]) ?>

<?= $this->render('_form', ['model' => $model]) ?>