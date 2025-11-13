<?php
include_once('./_common.php');
include_once('./cn_auth_code.php');

define('AJAX_AUTH_LIST', 'AUTH_LIST');
define('AJAX_AUTH_GET',  'AUTH_GET');
define('AJAX_AUTH_ADD',  'AUTH_ADD');
define('AJAX_AUTH_USE',  'AUTH_USE');
define('AJAX_AUTH_DEL',  'AUTH_DEL');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id         = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id      = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;
$phone      = isset($_REQUEST['phone']) ? $_REQUEST['phone'] : null;
$code       = isset($_REQUEST['code']) ? $_REQUEST['code'] : null;
$expires_dt = isset($_REQUEST['expires_dt']) ? $_REQUEST['expires_dt'] : null;
$used       = isset($_REQUEST['used']) && $_REQUEST['used'] !== '' ? intval($_REQUEST['used']) : 1;

if ($type === AJAX_AUTH_LIST) {
    $list = select_auth_code_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_AUTH_GET) {
    $row = select_auth_code_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_AUTH_ADD) {
    $ok = insert_auth_code($mb_id, $phone, $code, $expires_dt);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_AUTH_USE) {
    $ok = update_auth_code_used($id, $used);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_AUTH_DEL) {
    $ok = delete_auth_code($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
