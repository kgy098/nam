<?php
include_once('./_common.php');
include_once('./cn_teacher_time_block.php');

/* ===== 상수 선언 ===== */
// 교사 시간 블록
define('AJAX_TTB_LIST', 'TTB_LIST');   // 블록 리스트
define('AJAX_TTB_GET',  'TTB_GET');    // 단건 조회
define('AJAX_TTB_ADD',  'TTB_ADD');    // 블록 등록
define('AJAX_TTB_UPD',  'TTB_UPD');    // 블록 수정
define('AJAX_TTB_DEL',  'TTB_DEL');    // 블록 삭제

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id          = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id       = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;                    // 교사 mb_id
$target_date = isset($_REQUEST['target_date']) ? $_REQUEST['target_date'] : null;        // YYYY-MM-DD
$start_time  = isset($_REQUEST['start_time'])  ? $_REQUEST['start_time']  : null;        // HH:MM:SS
$end_time    = isset($_REQUEST['end_time'])    ? $_REQUEST['end_time']    : null;        // HH:MM:SS
$type_code   = isset($_REQUEST['ttb_type'])    ? $_REQUEST['ttb_type']    : null;        // 'AVAILABLE'|'BREAK'
$memo        = array_key_exists('memo', $_REQUEST) ? $_REQUEST['memo'] : '';             // nullable

if ($type === AJAX_TTB_SLOTS) {

    if (!$mb_id || !$target_date) {
        echo json_encode(['result' => 'FAIL', 'msg' => 'invalid params']);
        exit;
    }

    // 공통 슬롯 생성 (기존 _build_time_slots() 개선/호출)
    $slots = build_teacher_slots_common($mb_id, $target_date);

    echo json_encode([
        'result' => 'SUCCESS',
        'data'   => $slots
    ]);
    exit;
}
else if ($type === AJAX_TTB_LIST) {
    $list = select_teacher_time_block_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_TTB_GET) {
    $row = select_teacher_time_block_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} 

else if ($type === AJAX_TTB_ADD) {
    // 같은 시간대 BREAK 가 있는지 CRUD 로 조회
    $exist = select_teacher_break_one($mb_id, $target_date, $start_time, $end_time);

    if ($exist) {
        // 이미 있으면 UPDATE
        $ok = update_teacher_time_block(
            $exist['id'],
            $target_date,
            $start_time,
            $end_time,
            'BREAK',
            $memo
        );
    } else {
        // 없으면 INSERT
        $ok = insert_teacher_time_block(
            $mb_id,
            $target_date,
            $start_time,
            $end_time,
            'BREAK',
            $memo
        );
    }

    echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);

} 

else if ($type === AJAX_TTB_UPD) {
    $ok = update_teacher_time_block($id, $target_date, $start_time, $end_time, $type_code, $memo);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_TTB_DEL) {
    $ok = delete_teacher_time_block($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
