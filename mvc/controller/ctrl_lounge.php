<?php
include_once('./_common.php');
include_once('./cn_lounge.php');

define('AJAX_LOUNGE_LIST',   'LOUNGE_LIST');
define('AJAX_LOUNGE_GET',    'LOUNGE_GET');
define('AJAX_LOUNGE_ACTIVE', 'LOUNGE_ACTIVE');
define('AJAX_LOUNGE_ADD',    'LOUNGE_ADD');
define('AJAX_LOUNGE_UPD',    'LOUNGE_UPD');
define('AJAX_LOUNGE_DEL',    'LOUNGE_DEL');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id          = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$name        = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
$location    = array_key_exists('location', $_REQUEST) ? $_REQUEST['location'] : null;
$total_seats = isset($_REQUEST['total_seats']) && $_REQUEST['total_seats'] !== '' ? intval($_REQUEST['total_seats']) : null;
$is_active   = isset($_REQUEST['is_active']) && $_REQUEST['is_active'] !== '' ? intval($_REQUEST['is_active']) : null;

if ($type === AJAX_LOUNGE_LIST) {
    $list = select_lounge_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LOUNGE_GET) {
    $row = select_lounge_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_LOUNGE_ACTIVE) {
    $active = is_null($is_active) ? 1 : $is_active;
    $list = select_lounge_active($active, $start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_LOUNGE_ADD) {
    $ok = insert_lounge($name, $location, is_null($total_seats)?0:$total_seats, is_null($is_active)?1:$is_active);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_LOUNGE_UPD) {
    $ok = update_lounge($id, $name, $location, is_null($total_seats)?0:$total_seats, is_null($is_active)?1:$is_active);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_LOUNGE_DEL) {
    $ok = delete_lounge($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
