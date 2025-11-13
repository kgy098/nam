<?php
/* ctrl_lounge_seat.php */
include_once('./_common.php');
include_once('./cn_lounge_seat.php');

define('AJAX_LSEAT_LIST',      'LSEAT_LIST');
define('AJAX_LSEAT_GET',       'LSEAT_GET');
define('AJAX_LSEAT_BY_LOUNGE', 'LSEAT_BY_LOUNGE');
define('AJAX_LSEAT_ADD',       'LSEAT_ADD');
define('AJAX_LSEAT_UPD',       'LSEAT_UPD');
define('AJAX_LSEAT_DEL',       'LSEAT_DEL');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id         = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$lounge_id  = isset($_REQUEST['lounge_id']) && $_REQUEST['lounge_id'] !== '' ? intval($_REQUEST['lounge_id']) : null;
$seat_no    = array_key_exists('seat_no', $_REQUEST) ? $_REQUEST['seat_no'] : null;
$is_active  = isset($_REQUEST['is_active']) && $_REQUEST['is_active'] !== '' ? intval($_REQUEST['is_active']) : null;

if ($type === AJAX_LSEAT_LIST) {
    $list = select_lounge_seat_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LSEAT_GET) {
    $row = select_lounge_seat_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_LSEAT_BY_LOUNGE) {
    $only_active = (!is_null($is_active) ? (intval($is_active) === 1) : false);
    $list = select_lounge_seat_by_lounge(intval($lounge_id), $only_active, $start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LSEAT_ADD) {
    $ok = insert_lounge_seat(intval($lounge_id), $seat_no, is_null($is_active)?1:$is_active);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_LSEAT_UPD) {
    $ok = update_lounge_seat($id, intval($lounge_id), $seat_no, is_null($is_active)?1:$is_active);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_LSEAT_DEL) {
    $ok = delete_lounge_seat($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
