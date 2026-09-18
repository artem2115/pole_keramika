<?php
// vendor/noumo/easyii/modules/collections/views/a/create.php

$this->title = 'Создать коллекцию';
?>
<?= $this->render('_menu') ?>
<?= $this->render('_form', ['model' => $model]) ?>