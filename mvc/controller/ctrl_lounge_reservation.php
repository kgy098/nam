<?php
include_once('./_common.php');


$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id            = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id         = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;
$lounge_id     = isset($_REQUEST['lounge_id']) && $_REQUEST['lounge_id'] !== '' ? intval($_REQUEST['lounge_id']) : null;
$seat_id       = isset($_REQUEST['seat_id'])   && $_REQUEST['seat_id']   !== '' ? intval($_REQUEST['seat_id'])   : null;
$reserved_date = isset($_REQUEST['reserved_date']) ? $_REQUEST['reserved_date'] : null;
$start_time    = isset($_REQUEST['start_time'])    ? $_REQUEST['start_time']    : null;
$end_time      = isset($_REQUEST['end_time'])      ? $_REQUEST['end_time']      : null;
$status        = isset($_REQUEST['status'])        ? $_REQUEST['status']        : null;

if ($type === AJAX_LRES_LIST) {
  $list = select_lounge_reservation_list($start, $num);
  echo json_encode(!empty($list) ? ['result' => 'SUCCESS', 'data' => $list] : ['result' => 'FAIL']);
} else if ($type === AJAX_LRES_GET) {
  $row = select_lounge_reservation_one($id);
  echo json_encode(!empty($row) ? ['result' => 'SUCCESS', 'data' => $row] : ['result' => 'FAIL']);
} else if ($type === AJAX_LRES_BY_STUDENT) {
  $list = select_lounge_reservation_by_student($mb_id, $start, $num);
  echo json_encode(!empty($list) ? ['result' => 'SUCCESS', 'data' => $list] : ['result' => 'FAIL']);
} else if ($type === AJAX_LRES_BY_DATE) {
  $list = select_lounge_reservation_by_date($reserved_date, $lounge_id, $seat_id, $start, $num);
  echo json_encode(!empty($list) ? ['result' => 'SUCCESS', 'data' => $list] : ['result' => 'FAIL']);
} else if ($type === AJAX_LRES_ADD) {

  $mb_id         = $_SESSION['ss_mb_id'];
  $lounge_id     = $_POST['lounge_id'];
  $seat_id       = $_POST['seat_id'];
  $reserved_date = $_POST['reserved_date'];
  $start_time    = $_POST['start_time'];
  $end_time      = $_POST['end_time'];

  // 1) 하루 3개 초과 제한 체크
  $cnt = count_reservation_by_mb_date($mb_id, $reserved_date);
  if ($cnt > 3) {
    jres(false, '하루에 3개 초과 예약할 수 없습니다.');
  }

  // 2) 동일 좌석·동일 시간 중복 예약 체크
  $dup = exists_lounge_reservation($lounge_id, $seat_id, $reserved_date, $start_time);
  if ($dup) {
    jres(false, '이미 예약된 자리입니다.');
  }

  // 정상 추가
  $ok = insert_lounge_reservation(
    $mb_id,
    $lounge_id,
    $seat_id,
    $reserved_date,
    $start_time,
    $end_time,
    '예약'
  );
  if (!$ok) {
    jres(false, '예약 저장 중 오류가 발생했습니다.');
  }
  jres(true, 'SUCCESS');
} else if ($type === AJAX_LRES_UPD) {
  $ok = update_lounge_reservation($id, intval($lounge_id), intval($seat_id), $reserved_date, $start_time, $end_time, $status ?: '예약');
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else if ($type === AJAX_LRES_DEL) {
  $ok = delete_lounge_reservation($id);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else {
  echo json_encode(['result' => 'FAIL']);
}
