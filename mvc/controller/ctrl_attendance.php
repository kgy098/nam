<?php
include_once('./_common.php');

// 로그인 사용자 정보
$role = $member['role'] ?? '';
$type = $_REQUEST['type'] ?? '';

/*****************************************************************
 * 출결 단건 조회
 *****************************************************************/
if ($type === AJAX_ATT_GET) {

  $id  = (int)($_REQUEST['id'] ?? 0);
  $row = select_attendance_one($id);

  if (!$row) {
    jres(false, ['msg' => '데이터 없음']);
  }

  jres('ok', ['row' => $row]);
}
/*****************************************************************
 * 출결 리스트 (관리자)
 *****************************************************************/
else if ($type === AJAX_ATT_LIST) {

  $start = (int)($_REQUEST['start'] ?? 0);
  $rows  = (int)($_REQUEST['rows'] ?? CN_PAGE_NUM);

  $list = select_attendance_list($start, $rows);
  $cnt  = select_attendance_listcnt();

  jres('ok', ['list' => $list, 'total' => $cnt]);
} else if ($type === AJAX_ATT_OVERVIEW_LIST) {

  $page  = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
  $rows  = isset($_REQUEST['rows']) ? max(1, (int)$_REQUEST['rows']) : 7; // 날짜 7개 기본
  $start = ($page - 1) * $rows;

  $mb_id = esc($_REQUEST['mb_id'] ?? '');

  // 출결구분 개수
  $types = get_all_attendance_types();
  $typeCount = count($types);

  // 리스트
  $list = select_attendance_overview_list($start, $rows, $mb_id);

  $total = count($list);

  // elog("LIST11: " . print_r($list, true));

  jres('ok', [
    'list'  => $list,
    'total' => $total
  ]);
}

else if ($type === AJAX_ATT_ADMIN_LIST) {

  $start_date     = trim($_POST['start_date'] ?? '');
  $end_date       = trim($_POST['end_date'] ?? '');
  $class_id       = trim($_POST['class_id'] ?? '');
  $attend_type_id = trim($_POST['attend_type_id'] ?? '');

  // elog("ADMIN DATA: " . print_r($REQUEST, true));

  $list = select_attendance_admin_list($start_date, $end_date, $class_id, $attend_type_id);

  jres(true, [
    'list' => $list,
    'count' => count($list)
  ]);
}


/*****************************************************************
 * 출결 등록 (학생/관리자 공용)
 *****************************************************************/
else if ($type === AJAX_ATT_ADD) {

  $mb_id          = Esc($_REQUEST['mb_id'] ?? '');
  $attend_type_id = (int)($_REQUEST['attend_type_id'] ?? 0);
  $date           = trim($_REQUEST['date'] ?? '');

  // date 는 필수
  if ($mb_id === '' || $attend_type_id <= 0 || $date === '') {
    jres(false, ['msg' => '필수값이 누락되었습니다.']);
  }

  // 안전한 날짜 형식으로 잘라줌
  $date = substr($date, 0, 16);

  // 해당 날짜 + type 데이터 있는지 조회 (CRUD)
  $row = select_attendance_one_by_key($mb_id, $date, $attend_type_id);

  if ($row) {
    // 이미 있으면 update (출석완료)
    $ok = update_attendance($row['id'], null, $date . ' 00:00:00', '출석완료');
    jres($ok, ['msg' => $ok ? '출석완료로 수정됨' : '수정 실패']);
  }

  // 없으면 insert
  $ok = insert_attendance($mb_id, $attend_type_id, $date);
  jres($ok, ['msg' => $ok ? '출석 등록 완료' : '등록 실패']);
}

/*****************************************************************
 * 출결 수정 (관리자 전용)
 *****************************************************************/
else if ($type === AJAX_ATT_UPD) {

  if ($role === '학생') {
    jres('', ['msg' => '권한 없음']);
  }

  $id             = (int)($_REQUEST['id'] ?? 0);
  $attend_type_id = $_REQUEST['attend_type_id'] ?? null;
  $attend_dt      = $_REQUEST['attend_dt'] ?? null;
  $status         = $_REQUEST['status'] ?? null;

  // ENUM 검증
  if (!is_null($status) && !in_array($status, ['출석완료', '미출석'], true)) {
    jres('', ['msg' => 'status 오류']);
  }

  $ok = update_attendance($id, $attend_type_id, $attend_dt, $status);
  jres($ok ? 'ok' : '', ['msg' => $ok ? '수정 완료' : '수정 실패']);
}

/*****************************************************************
 * 출결 삭제 (관리자)
 *****************************************************************/
else if ($type === AJAX_ATT_DEL) {

  if ($role === '학생') {
    jres('', ['msg' => '권한 없음']);
  }

  $id = (int)($_REQUEST['id'] ?? 0);
  $ok = delete_attendance($id);

  jres($ok ? 'ok' : '', ['msg' => $ok ? '삭제 완료' : '삭제 실패']);
}

/*****************************************************************
 * 잘못된 요청
 *****************************************************************/
else {
  jres('', ['msg' => '잘못된 요청']);
}
