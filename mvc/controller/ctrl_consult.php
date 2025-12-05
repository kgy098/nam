<?php
include_once('./_common.php');

$type = $_REQUEST['type'] ?? '';

$student_mb_id = esc($_REQUEST['student_mb_id'] ?? '');
$teacher_mb_id = esc($_REQUEST['teacher_mb_id'] ?? '');
$target_date   = esc($_REQUEST['target_date'] ?? '');
$scheduled_dt  = esc($_REQUEST['scheduled_dt'] ?? '');
$id            = intval($_REQUEST['id'] ?? 0);


/* ============================================================
 * 날짜 리스트 생성 (14일)
 * ============================================================ */
function _build_date_list($days = 14)
{
  $list = [];
  for ($i = 0; $i < $days; $i++) {
    $d = date('Y-m-d', strtotime("+{$i} day"));
    $list[] = $d;
  }
  return $list;
}



/* ============================================================
 * Controller
 * ============================================================ */

if ($type === AJAX_CONSULT_TEACHER_LIST) {
  $rows = sql_query("select mb_id, mb_name from g5_member where role='TEACHER' order by mb_name asc");
  $list = [];
  while ($r = sql_fetch_array($rows)) $list[] = $r;
  jres(true, $list);
} else if ($type === AJAX_CONSULT_DATE_LIST) {

  $dates = _build_date_list(14);
  jres(true, $dates);
} else if ($type === AJAX_CONSULT_AVAILABLE_TIMES) {

  if ($teacher_mb_id === '' || $target_date === '') jres(false, 'required');

  $consult_type = esc($_REQUEST['consult_type'] ?? '');

  $slots = _build_time_slots($teacher_mb_id, $target_date, $student_mb_id, $consult_type);
  jres(true, $slots);
} else if ($type === AJAX_CONSULT_RESERVE) {

  if ($student_mb_id === '' || $teacher_mb_id === '' || $scheduled_dt === '') {
    jres(false, 'required');
  }

  $consult_type = esc($_REQUEST['consult_type'] ?? '');
  elog(print_r($_REQUEST, true));

  // 중복 체크
  $exist = select_consult_by_teacher_and_datetime($teacher_mb_id, $consult_type, $scheduled_dt);
  if ($exist && $exist['student_mb_id'] !== $student_mb_id) {
    jres(false, '이미 예약된 시간입니다.');
  }

  $ok = insert_consult_slot($student_mb_id, $teacher_mb_id, $consult_type, $scheduled_dt);
  if (!$ok) jres(false, 'insert fail');

  jres(true, 'ok');
} else if ($type === AJAX_CONSULT_CANCEL) {

  if ($id <= 0) jres(false, 'invalid id');

  $row = select_consult_one($id);
  if (!$row) jres(false, 'not found');

  delete_consult($id);
  jres(true, 'deleted');
} else if ($type === AJAX_CONSULT_MY_LIST) {

  if ($student_mb_id === '') jres(false, 'required');

  $consult_type = esc($_REQUEST['consult_type'] ?? '');

  $list = select_consult_by_student($student_mb_id, $consult_type);
  jres(true, $list);
}

/* ============================================================
 * 앱 선생님 화면용
 * ============================================================ */
else if ($type === 'CONSULT_TEACHER_MY_LIST') {

  $teacher_mb_id = Esc($_POST['teacher_mb_id'] ?? '');
  $consult_type  = Esc($_POST['consult_type'] ?? '');
  $target_date   = Esc($_POST['target_date'] ?? '');

  if ($teacher_mb_id === '' || $consult_type === '' || $target_date === '') {
    jres(false, '필수값이 누락되었습니다.');
  }

  // ★ 슬롯 전체 생성 + 예약 반영 + 휴게/불가 반영 + teacher mode 적용
  $slots = _build_time_slots(
    $teacher_mb_id,
    $target_date,
    '',           // 선생님 화면은 student_mb_id 비교 필요 없음
    'teacher'     // ← 핵심
  );

  jres(true, ['list' => $slots]);
}


/* ============================================================
 * 기존 CONSULT 기능 (관리자/기존 페이지 용)
 * ============================================================ */ else if ($type === AJAX_CONSULT_GET) {

  $row = select_consult_one($id);
  if (!$row) jres(false, 'not found');
  jres(true, $row);
} else if ($type === AJAX_CONSULT_LIST) {

  $list = select_consult_list(0, 200);
  jres(true, $list);
} else if ($type === AJAX_CONSULT_DELETE) {

  if ($id <= 0) jres(false, 'invalid');
  delete_consult($id);
  jres(true, 'deleted');
} else {
  jres(false, 'invalid type');
}
