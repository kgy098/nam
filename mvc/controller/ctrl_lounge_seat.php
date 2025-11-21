<?php
/* ctrl_lounge_seat.php */
include_once('./_common.php');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$num   = isset($_REQUEST['num'])   ? (int)$_REQUEST['num']   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id        = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$lounge_id = isset($_REQUEST['lounge_id']) && $_REQUEST['lounge_id'] !== '' ? (int)$_REQUEST['lounge_id'] : 0;
$cell_no   = isset($_REQUEST['cell_no'])   && $_REQUEST['cell_no']   !== '' ? (int)$_REQUEST['cell_no']   : 0;

$seat_no   = isset($_REQUEST['seat_no']) ? trim($_REQUEST['seat_no']) : '';
$is_active = isset($_REQUEST['is_active']) && $_REQUEST['is_active'] !== '' ? (int)$_REQUEST['is_active'] : 1;


/* -----------------------------------------
   1) 전체 좌석 리스트
----------------------------------------- */
if ($type === AJAX_LSEAT_LIST) {

    $list = select_lounge_seat_list($start, $num);

    if ($list === false) {
        echo json_encode(['result' => 'FAIL']);
    } else {
        echo json_encode([
            'result' => 'SUCCESS',
            'data'   => $list
        ]);
    }


/* -----------------------------------------
   2) 단건 조회
----------------------------------------- */
} else if ($type === AJAX_LSEAT_GET) {

    $row = select_lounge_seat_one($id);

    if (!empty($row)) {
        echo json_encode(['result' => 'SUCCESS', 'data' => $row]);
    } else {
        echo json_encode(['result' => 'FAIL']);
    }


/* -----------------------------------------
   3) 특정 라운지 좌석 목록
      - only_active: is_active === 1 인 경우만 true
      - 결과 0건이어도 SUCCESS + data:[]
----------------------------------------- */
} else if ($type === AJAX_LSEAT_BY_LOUNGE) {

    $only_active = ($is_active === 1 ? true : false);

    $list = select_lounge_seat_by_lounge($lounge_id, $only_active, $start, $num);

    if ($list === false) {
        echo json_encode(['result' => 'FAIL']);
    } else {
        echo json_encode([
            'result' => 'SUCCESS',
            'data'   => $list ?: []
        ]);
    }


/* -----------------------------------------
   4) 좌석 등록
   - lounge_id, cell_no, seat_no 사용
----------------------------------------- */
} else if ($type === AJAX_LSEAT_ADD) {

    $ok = insert_lounge_seat($lounge_id, $cell_no, $seat_no, $is_active);

    echo json_encode($ok
        ? ['result' => 'SUCCESS']
        : ['result' => 'FAIL']
    );


/* -----------------------------------------
   5) 좌석 수정
----------------------------------------- */
} else if ($type === AJAX_LSEAT_UPD) {

    $ok = update_lounge_seat($id, $lounge_id, $cell_no, $seat_no, $is_active);

    echo json_encode($ok
        ? ['result' => 'SUCCESS']
        : ['result' => 'FAIL']
    );


/* -----------------------------------------
   6) 좌석 삭제 (hard delete)
----------------------------------------- */
} else if ($type === AJAX_LSEAT_DEL) {

    $ok = delete_lounge_seat($id);

    echo json_encode($ok
        ? ['result' => 'SUCCESS']
        : ['result' => 'FAIL']
    );


/* -----------------------------------------
   7) 정의되지 않은 type
----------------------------------------- */
} else {
    echo json_encode(['result' => 'FAIL']);
}
