<?php
//app/views/catalog/_sidebar_categories.php
/**
 * @var \yii\easyii\modules\catalog\models\Category $category
 * @var \yii\easyii\modules\catalog\models\Category[] $allCategories
 * @var string $view
 */

use yii\helpers\Html;
use yii\helpers\Url;

if (empty($allCategories)) return;
?>

<div class="sb1">
  <div class="sb1-ttl">
    <b>Категория</b>
    <i></i>
  </div>
  <div class="sb1-b">
    <ul>
      <?php foreach ($allCategories as $cat): ?>
        <li>
          <a class="<?= $category && $cat->category_id === $category->category_id ? 'active' : '' ?>"
             href="<?= Url::to(['/catalog/' . $cat->slug . '/' . ($view === 'kollekcii' ? 'kollekcii' : 'tovary')]) ?>">
            <?= Html::encode($cat->title) ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>
  </div>
</div>