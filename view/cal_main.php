<?
include_once('./_common.php');

$g5['title'] = "학사일정&공지";
$menu_group = 'cal';

include_once('./head.php');
?>



<section class="submain-list">

  <div class="list-box">
    <span>공지사항</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </div>

  <div class="list-box">
    <span>학사일정</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </div>

</section>

<? include_once('./tail.php'); ?>