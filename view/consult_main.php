<?
include_once('./_common.php');

$g5['title'] = "상담&예약";
$menu_group = 'consult';

include_once('./head.php');
?>



<section class="submain-list">

  <div class="list-box">
    <span>스터디라운지 예약</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </div>

  <div class="list-box">
    <span>학과상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </div>

  <div class="list-box">
    <span>멘토상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </div>

  <div class="list-box">
    <span>비대면 질의응답</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </div>

</section>

<? include_once('./tail.php'); ?>