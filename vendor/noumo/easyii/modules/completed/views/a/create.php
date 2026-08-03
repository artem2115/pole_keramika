<?php
//vendor/noumo/easyii/modules/completed/views/a/create.php
$this->title = Yii::t('easyii/completed', 'Create completed');
?>
<?= $this->render('_menu') ?>
<?= $this->render('_form', ['model' => $model]) ?>