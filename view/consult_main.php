<?
include_once('./_common.php');

$g5['title'] = "상담&예약";
$menu_group = 'consult';

include_once('./head.php');

if ( $member['role']=='STUDENT' ) {
  $consult_url = G5_VIEW_URL . "/consult/consult_list.php";
  $mento_url = G5_VIEW_URL . "/mento/mento_list.php";
} else if ( $member['role']=='TEACHER' ) {
  $consult_url = G5_VIEW_URL . "/consult/consult_teacher_list.php";
  $mento_url = G5_VIEW_URL . "/mento/mento_teacher_list.php";
}
?>



<section class="submain-list">

  <? if ( $member['role']=='STUDENT' ) { ?>
  <a href="<?= G5_VIEW_URL ?>/lounge_reservation/lounge_reservation_list.php" class="list-box">
    <span>스터디라운지 예약</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>
  <? } ?>

  <a href="<?= $consult_url ?>" class="list-box">
    <span>학과상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a href="<?= $mento_url ?>" class="list-box">
    <span>멘토상담</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <a href="<?= G5_VIEW_URL ?>/qna/qna_list.php" class="list-box">
    <span>비대면 질의응답</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>

  <? if ( $member['role']=='TEACHER' ) { ?>
  <a href="<?= G5_VIEW_URL ?>/teacher_time_block/teacher_time_block_list.php" class="list-box">
    <span>질문잠금 관리</span>
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="arrow">
  </a>
  <? } ?>

</section>

<? include_once('./tail.php'); ?>