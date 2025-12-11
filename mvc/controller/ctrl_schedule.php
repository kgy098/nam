<?php
include_once('./_common.php');

define('AJAX_SCHEDULE_LIST',   'SCHEDULE_LIST');
define('AJAX_SCHEDULE_GET',    'SCHEDULE_GET');
define('AJAX_SCHEDULE_ADD',    'SCHEDULE_ADD');
define('AJAX_SCHEDULE_UPD',    'SCHEDULE_UPD');
define('AJAX_SCHEDULE_DEL',    'SCHEDULE_DEL');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id          = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id       = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;
$title       = isset($_REQUEST['title']) ? $_REQUEST['title'] : null;
$description = array_key_exists('description', $_REQUEST) ? $_REQUEST['description'] : null;
$start_date  = isset($_REQUEST['start_date']) ? $_REQUEST['start_date'] : null;
$end_date    = isset($_REQUEST['end_date']) ? $_REQUEST['end_date'] : null;

if ($type === AJAX_SCHEDULE_LIST) {
    $list = select_schedule_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_SCHEDULE_GET) {
    $row = select_schedule_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_SCHEDULE_ADD) {
    $ok = insert_schedule($mb_id, $title, $description, $start_date, $end_date);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_SCHEDULE_UPD) {
    $ok = update_schedule($id, $title, $description, $start_date, $end_date);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_SCHEDULE_DEL) {
    $ok = delete_schedule($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
