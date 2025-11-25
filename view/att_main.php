<?
include_once('./_common.php');

$g5['title'] = "학습&출결";
$menu_group = 'att';

include_once('./head.php');
?>



<section class="submain-list">

  <a href="/view/video/video_list.php" class="list-box">
    <span>수업영상</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a class="list-box">
    <span>학습보고서</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a class="list-box">
    <span>모의고사 신청</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a class="list-box">
    <span>출결 관리</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

</section>

<? include_once('./tail.php'); ?>