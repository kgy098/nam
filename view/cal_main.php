<?
include_once('./_common.php');

$g5['title'] = "학사일정&공지";
$menu_group = 'cal';

include_once('./head.php');
?>



<section class="submain-list">

  <a href="<?= G5_VIEW_URL ?>/notice/notice_list.php" class="list-box">
    <span>공지사항</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a href="<?= G5_VIEW_URL ?>/schedule/schedule_list.php" class="list-box">
    <span>학사일정</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

</section>

<? include_once('./tail.php'); ?>