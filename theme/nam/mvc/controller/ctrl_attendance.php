<?php
include_once('./_common.php');
include_once('./cn_attendance.php');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id    = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;

$attend_type_id = isset($_REQUEST['attend_type_id']) && $_REQUEST['attend_type_id'] !== '' ? intval($_REQUEST['attend_type_id']) : null;
$attend_dt      = isset($_REQUEST['attend_dt']) ? $_REQUEST['attend_dt'] : null;
$atype          = isset($_REQUEST['atype']) ? $_REQUEST['atype'] : null;
$status         = isset($_REQUEST['status']) ? $_REQUEST['status'] : null;

$from_dt = isset($_REQUEST['from_dt']) ? $_REQUEST['from_dt'] : null;
$to_dt   = isset($_REQUEST['to_dt']) ? $_REQUEST['to_dt'] : null;

if ($type == AJAX_ATT_LIST) {
    $list = select_attendance_list($start, $num);
    if (!empty($list)) echo json_encode(['result'=>'SUCCESS','data'=>$list]);
    else echo json_encode(['result'=>'FAIL']);
} else if ($type == AJAX_ATT_GET) {
    $row = select_attendance_one($id);
    if (!empty($row)) echo json_encode(['result'=>'SUCCESS','data'=>$row]);
    else echo json_encode(['result'=>'FAIL']);
} else if ($type == AJAX_ATT_BY_STUDENT) {
    $list = select_attendance_by_student($mb_id, $start, $num);
    if (!empty($list)) echo json_encode(['result'=>'SUCCESS','data'=>$list]);
    else echo json_encode(['result'=>'FAIL']);
} else if ($type == AJAX_ATT_BETWEEN) {
    $list = select_attendance_between($from_dt, $to_dt, $mb_id, $atype, $status, $start, $num);
    if (!empty($list)) echo json_encode(['result'=>'SUCCESS','data'=>$list]);
    else echo json_encode(['result'=>'FAIL']);
} else if ($type == AJAX_ATT_ADD) {
    $ok = insert_attendance($mb_id, $attend_type_id, $attend_dt, $atype ? $atype : '입실', $status ? $status : '출석');
    if ($ok) echo json_encode(['result'=>'SUCCESS']);
    else echo json_encode(['result'=>'FAIL']);
} else if ($type == AJAX_ATT_UPD) {
    $ok = update_attendance($id, $attend_type_id, $attend_dt, $atype, $status);
    if ($ok) echo json_encode(['result'=>'SUCCESS']);
    else echo json_encode(['result'=>'FAIL']);
} else if ($type == AJAX_ATT_DEL) {
    $ok = delete_attendance($id);
    if ($ok) echo json_encode(['result'=>'SUCCESS']);
    else echo json_encode(['result'=>'FAIL']);
} else {
    echo json_encode(['result'=>'FAIL']);
}
?>
