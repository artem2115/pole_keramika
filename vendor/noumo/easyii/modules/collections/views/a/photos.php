<?php
// vendor/noumo/easyii/modules/collections/views/a/photos.php

use yii\easyii\widgets\Photos;

$this->title = $model->title;
?>

<?= $this->render('_menu') ?>
<?= $this->render('_submenu', ['model' => $model]) ?>

<?= Photos::widget(['model' => $model]) ?>