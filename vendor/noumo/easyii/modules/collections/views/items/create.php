<?php
//vendor/noumo/easyii/modules/collections/views/items/create.php
$this->title = Yii::t('easyii/collections', 'Create item');
?>
<?= $this->render('_menu', ['category' => $category]) ?>
<?= $this->render('_form', ['model' => $model, 'dataForm' => $dataForm]) ?>