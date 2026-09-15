<?php
//app/views/ajax/_modal_3d_project.php
$modalClass = !empty($modal_class) ? $modal_class : 'm1';
?>
<div class="modalWrap">
  <div class="modal <?= $modalClass ?> fade" tabindex="-1" role="dialog">
    <div class="close"></div>
    <div class="mBody">
      <div class="modal-header">
        <h3>БЕСПЛАТНЫЙ 3D проект</h3>
      </div>
      <div class="modal-body">
        <p><img src="/i/3d.png" alt=""></p>
        <p>Воплотим в реальность даже самые смелые задумки, поможем определиться с концепцией</p>
        <div class="b12">
          <button class="btn1-b1" data-modal="get_3d_project">Оставить заявку</button>
          <span>Экономия до 15 000 ₽</span>
        </div>
      </div>
    </div>
  </div>
</div>