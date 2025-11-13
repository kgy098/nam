<?php
include_once('./_common.php');
include_once('./cn_member_product.php');

define('AJAX_MP_LIST', 'MP_LIST');
define('AJAX_MP_GET',  'MP_GET');
define('AJAX_MP_ADD',  'MP_ADD');
define('AJAX_MP_UPD',  'MP_UPD');
define('AJAX_MP_DEL',  'MP_DEL');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id                = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id             = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;
$product_id        = isset($_REQUEST['product_id']) && $_REQUEST['product_id'] !== '' ? intval($_REQUEST['product_id']) : null;
$checkin_datetime  = isset($_REQUEST['checkin_datetime'])  ? $_REQUEST['checkin_datetime']  : null;
$checkout_datetime = isset($_REQUEST['checkout_datetime']) ? $_REQUEST['checkout_datetime'] : null;
$status            = isset($_REQUEST['status']) ? $_REQUEST['status'] : null; // '신청','입실','퇴실','취소'
$room_no           = array_key_exists('room_no', $_REQUEST) ? $_REQUEST['room_no'] : '';
$memo              = array_key_exists('memo', $_REQUEST) ? $_REQUEST['memo'] : '';

if ($type === AJAX_MP_LIST) {
    $list = select_member_product_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_MP_GET) {
    $row = select_member_product_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_MP_ADD) {
    $ok = insert_member_product($mb_id, intval($product_id), $checkin_datetime, $status ?: '입실', $room_no ?: '', $memo ?: '');
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_MP_UPD) {
    $ok = update_member_product($id, $checkout_datetime, $status, $room_no, $memo);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_MP_DEL) {
    $ok = delete_member_product($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
