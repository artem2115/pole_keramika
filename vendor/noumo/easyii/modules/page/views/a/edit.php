<?php
//vendor/noumo/easyii/modules/page/views/a/edit.php
$this->title = Yii::t('easyii/page', 'Edit page');
?>
<?= $this->render('_menu') ?>
<?= $this->render('_form', ['model' => $model]) ?>