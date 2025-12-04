<?
include_once('./_common.php');

$g5['title'] = "상담&예약";
$menu_group = 'consult';

include_once('./head.php');
?>



<section class="submain-list">

  <? if ( $member['role']=='STUDENT' ) { ?>
  <a href="<?= G5_VIEW_URL ?>/lounge_reservation/lounge_reservation_list.php" class="list-box">
    <span>스터디라운지 예약</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>
  <? } ?>

  <? if ( $member['role']=='STUDENT' ) { ?>
  <a href="<?= G5_VIEW_URL ?>/consult/consult_list.php" class="list-box">
    <span>학과상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>
  <? } else {?>
  <a href="<?= G5_VIEW_URL ?>/consult/consult_teacher_list.php" class="list-box">
    <span>학과상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>
  <? } ?>

  <a href="<?= G5_VIEW_URL ?>/mento/mento_list.php" class="list-box">
    <span>멘토상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a href="<?= G5_VIEW_URL ?>/qna/qna_list.php" class="list-box">
    <span>비대면 질의응답</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

</section>

<? include_once('./tail.php'); ?>