<?php
include_once('./_common.php');

// ★ CRUD 파일은 공통에서 이미 include 되므로 다시 include하지 않음
// include_once('./cn_lounge.php');  // 금지 규칙

// -----------------------------------------------------
// 파라미터 정리
// -----------------------------------------------------
$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$num   = isset($_REQUEST['num'])   ? (int)$_REQUEST['num']   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id          = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$name        = isset($_REQUEST['name']) ? trim($_REQUEST['name']) : '';
$location    = isset($_REQUEST['location']) ? trim($_REQUEST['location']) : '';
$total_seats = isset($_REQUEST['total_seats']) && $_REQUEST['total_seats'] !== '' ? (int)$_REQUEST['total_seats'] : 0;
$is_active   = isset($_REQUEST['is_active']) && $_REQUEST['is_active'] !== '' ? (int)$_REQUEST['is_active'] : 1;

// 1) 라운지 리스트
if ($type === AJAX_LOUNGE_LIST) {

    $list = select_lounge_list($start, $num);
    echo json_encode(!empty($list) ? ['result' => 'SUCCESS', 'data' => $list]: ['result' => 'FAIL']);

// 2) 단건 조회
} else if ($type === AJAX_LOUNGE_GET) {

    $row = select_lounge_one($id);
    echo json_encode(!empty($row)
        ? ['result' => 'SUCCESS', 'data' => $row]
        : ['result' => 'FAIL']
    );

// 3) 활성/비활성 목록 조회
} else if ($type === AJAX_LOUNGE_ACTIVE) {

    $list = select_lounge_active($is_active, $start, $num);
    echo json_encode(!empty($list)
        ? ['result' => 'SUCCESS', 'data' => $list]
        : ['result' => 'FAIL']
    );

// 4) 등록
} else if ($type === AJAX_LOUNGE_ADD) {

    $ok = insert_lounge($name, $location, $total_seats, $is_active);
    echo json_encode($ok
        ? ['result' => 'SUCCESS']
        : ['result' => 'FAIL']
    );

// 5) 수정
} else if ($type === AJAX_LOUNGE_UPD) {

    $ok = update_lounge($id, $name, $location, $total_seats, $is_active);
    echo json_encode($ok
        ? ['result' => 'SUCCESS']
        : ['result' => 'FAIL']
    );

// 6) 삭제(soft delete)
} else if ($type === AJAX_LOUNGE_DEL) {

    $ok = delete_lounge($id);
    echo json_encode($ok
        ? ['result' => 'SUCCESS']
        : ['result' => 'FAIL']
    );

// 7) 그 외
} else {
    echo json_encode(['result' => 'FAIL']);
}
