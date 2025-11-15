<?php
include_once('./_common.php');
include_once('./cn_lounge_reservation.php');

define('AJAX_LRES_LIST',       'LRES_LIST');
define('AJAX_LRES_GET',        'LRES_GET');
define('AJAX_LRES_BY_STUDENT', 'LRES_BY_STUDENT');
define('AJAX_LRES_BY_DATE',    'LRES_BY_DATE');
define('AJAX_LRES_ADD',        'LRES_ADD');
define('AJAX_LRES_UPD',        'LRES_UPD');
define('AJAX_LRES_DEL',        'LRES_DEL');

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
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS', 'data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LRES_GET) {
    $row = select_lounge_reservation_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS', 'data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_LRES_BY_STUDENT) {
    $list = select_lounge_reservation_by_student($mb_id, $start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS', 'data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LRES_BY_DATE) {
    $list = select_lounge_reservation_by_date($reserved_date, $lounge_id, $seat_id, $start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS', 'data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LRES_ADD) {
    $ok = insert_lounge_reservation($mb_id, intval($lounge_id), intval($seat_id), $reserved_date, $start_time, $end_time, $status ?: '예약');
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_LRES_UPD) {
    $ok = update_lounge_reservation($id, intval($lounge_id), intval($seat_id), $reserved_date, $start_time, $end_time, $status ?: '예약');
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_LRES_DEL) {
    $ok = delete_lounge_reservation($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
