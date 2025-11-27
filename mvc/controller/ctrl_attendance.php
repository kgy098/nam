<?php
include_once('./_common.php');
// cn_attendance.php 는 공통에서 include 되고 있으므로 여기서는 추가 include 하지 않음

header('Content-Type: application/json; charset=utf-8');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id    = isset($_REQUEST['id'])    ? intval($_REQUEST['id'])    : 0;
$mb_id = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id']         : null;

$attend_type_id = (isset($_REQUEST['attend_type_id']) && $_REQUEST['attend_type_id'] !== '')
  ? intval($_REQUEST['attend_type_id'])
  : null;

$attend_dt = isset($_REQUEST['attend_dt']) ? $_REQUEST['attend_dt'] : null;
// $atype (입실/퇴실 등) 은 type 컬럼 삭제로 더이상 사용하지 않음
$status   = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;

// 기간 검색용
$from_dt = isset($_REQUEST['from_dt']) ? $_REQUEST['from_dt'] : null;
$to_dt   = isset($_REQUEST['to_dt'])   ? $_REQUEST['to_dt']   : null;

// 출결현황(OUTER JOIN)용 파라미터
$attend_date = isset($_REQUEST['attend_date']) ? $_REQUEST['attend_date'] : null; // YYYY-MM-DD
$class    = (isset($_REQUEST['class']) && $_REQUEST['class'] !== '')
  ? intval($_REQUEST['class'])
  : null;

if ($type == AJAX_ATT_LIST) {

  $list  = select_attendance_list($start, $num);
  $total = select_attendance_listcnt();

  if (!empty($list)) {
    echo json_encode([
      'result' => 'SUCCESS',
      'data'   => $list,
      'total'  => $total
    ]);
  } else {
    echo json_encode(['result' => 'FAIL']);
  }
} else if ($type == AJAX_ATT_BETWEEN) {

  // from_dt, to_dt 없으면 잘못된 BETWEEN 쿼리 방지
  if (empty($from_dt) || empty($to_dt)) {
    echo json_encode(['result' => 'FAIL', 'message' => 'INVALID_DATE_RANGE']);
  } else {
    // CRUD 쪽에서 이미 type 파라미터 제거됨: ($from_dt, $to_dt, $mb_id=null, $status=null, $start=0, $num=CN_PAGE_NUM)
    $list = select_attendance_between($from_dt, $to_dt, $mb_id, $status, $start, $num);

    if (!empty($list)) {
      echo json_encode(['result' => 'SUCCESS', 'data' => $list]);
    } else {
      echo json_encode(['result' => 'FAIL']);
    }
  }
} else if ($type == AJAX_ATT_ADD) {

  if (!$mb_id) {
    echo json_encode(['result' => 'FAIL', 'message' => 'NO_MB_ID']);
    exit;
  }

  // 학생 정보 조회
  $stu = sql_fetch("SELECT mb_id, auth_no, mb_sex FROM g5_member WHERE mb_id = '{$mb_id}'");
  if (!$stu) {
    echo json_encode(['result' => 'FAIL', 'message' => 'INVALID_MB']);
    exit;
  }

  // 입력 인증번호
  $input_auth = trim($_REQUEST['auth_no'] ?? '');

  // ① 인증번호 체크
  if ($stu['auth_no'] !== $input_auth) {
    echo json_encode(['result' => 'FAIL', 'message' => 'AUTH_FAIL']);
    exit;
  }

  // ② 중복 출석 방지 (오늘 + 동일 타입 존재 여부)
  $type_id = intval($_REQUEST['attend_type_id'] ?? 0);

  $dup = sql_fetch("
        SELECT id 
        FROM cn_attendance
        WHERE mb_id = '{$mb_id}'
          AND attend_type_id = {$type_id}
          AND DATE(attend_dt) = CURDATE()
        LIMIT 1
    ");

  if ($dup) {
    echo json_encode(['result' => 'FAIL', 'message' => 'DUPLICATE']);
    exit;
  }

  // ③ 서버에서 NOW(), status=출석완료 로 insert
  $ok = insert_attendance(
    $mb_id,
    $type_id
  );

  if ($ok) {
    echo json_encode(['result' => 'SUCCESS']);
  } else {
    echo json_encode(['result' => 'FAIL', 'message' => 'INSERT_FAIL']);
  }
  exit;
} else if ($type == AJAX_ATT_UPD) {

  // update_attendance($id, $attend_type_id=null, $attend_dt=null, $status=null)
  $ok = update_attendance(
    $id,
    $attend_type_id,
    $attend_dt,
    $status
  );

  if ($ok) {
    echo json_encode(['result' => 'SUCCESS']);
  } else {
    echo json_encode(['result' => 'FAIL']);
  }
} else if ($type == AJAX_ATT_DEL) {

  $ok = delete_attendance($id);
  if ($ok) {
    echo json_encode(['result' => 'SUCCESS']);
  } else {
    echo json_encode(['result' => 'FAIL']);
  }

  // ========================================================================
  // 출결현황: 학생 × 출결구분 전체 조합 + 기간 OUTER JOIN
  // ========================================================================
} else if ($type == AJAX_ATT_STATUS_LIST) {

  if (!$start_date || !$end_date) {
    echo json_encode(['result' => 'FAIL', 'message' => 'INVALID_DATE_RANGE']);
    exit;
  }

  $list  = select_attendance_status($start_date, $end_date, $class, $attend_type_id, $start, $num);
  $total = select_attendance_status_cnt($start_date, $end_date, $class, $attend_type_id);

  echo json_encode([
    'result' => 'SUCCESS',
    'data'   => $list,
    'total'  => $total
  ]);
  exit;
} else if ($type == AJAX_ATT_STATUS_CNT) {

  if (!$start_date || !$end_date) {
    echo json_encode(['result' => 'FAIL', 'message' => 'INVALID_DATE_RANGE']);
    exit;
  }

  $total = select_attendance_status_cnt($start_date, $end_date, $class, $attend_type_id);

  echo json_encode([
    'result' => 'SUCCESS',
    'total'  => $total
  ]);
  exit;
} else {
  echo json_encode(['result' => 'FAIL']);
}
