<?php
include_once('./_common.php');
include_once('./cn_product.php');

define('AJAX_PRODUCT_LIST',   'PRODUCT_LIST');
define('AJAX_PRODUCT_GET',    'PRODUCT_GET');
define('AJAX_PRODUCT_ACTIVE', 'PRODUCT_ACTIVE');
define('AJAX_PRODUCT_ADD',    'PRODUCT_ADD');
define('AJAX_PRODUCT_UPD',    'PRODUCT_UPD');
define('AJAX_PRODUCT_DEL',    'PRODUCT_DEL');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id           = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$mb_id        = isset($_REQUEST['mb_id']) ? $_REQUEST['mb_id'] : null;
$it_id        = array_key_exists('it_id', $_REQUEST) ? $_REQUEST['it_id'] : null;
$name         = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;
$type_code    = isset($_REQUEST['type_code']) ? $_REQUEST['type_code'] : null; // 'ROOM'|'PROGRAM'|'ETC'
$description  = array_key_exists('description', $_REQUEST) ? $_REQUEST['description'] : null;
$base_amount  = isset($_REQUEST['base_amount']) && $_REQUEST['base_amount'] !== '' ? intval($_REQUEST['base_amount']) : null;
$period_type  = isset($_REQUEST['period_type']) ? $_REQUEST['period_type'] : null; // 'MONTH'|'DAY'|'TERM'
$is_active    = isset($_REQUEST['is_active']) && $_REQUEST['is_active'] !== '' ? intval($_REQUEST['is_active']) : null;
$sort_order   = isset($_REQUEST['sort_order']) && $_REQUEST['sort_order'] !== '' ? intval($_REQUEST['sort_order']) : null;

if ($type === AJAX_PRODUCT_LIST) {
    $list = select_product_list($start, $num);
    echo json_encode(!empty($list) ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);

} else if ($type === AJAX_PRODUCT_GET) {
    $row = select_product_one($id);
    echo json_encode(!empty($row) ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_PRODUCT_ACTIVE) {
    $active = is_null($is_active) ? 1 : $is_active;
    $list = select_product_list($start, $num);
    $filtered = [];
    foreach ($list as $r) {
        if (isset($r['is_active']) && intval($r['is_active']) === intval($active)) $filtered[] = $r;
    }
    echo json_encode(!empty($filtered) ? ['result'=>'SUCCESS','data'=>$filtered] : ['result'=>'FAIL']);

} else if ($type === AJAX_PRODUCT_ADD) {
    $ok = insert_product(
        $mb_id,
        $name,
        $type_code ?: 'ROOM',
        $description,
        is_null($base_amount) ? 0 : $base_amount,
        $period_type ?: 'MONTH',
        is_null($is_active) ? 1 : $is_active,
        is_null($sort_order) ? 0 : $sort_order
    );
    if ($ok && $it_id) {
        // 별도 it_id 연동이 필요하면 업데이트로 반영
        $row = sql_fetch("select max(id) as maxid from cn_product");
        if ($row && $row['maxid']) {
            sql_query("update cn_product set it_id = '{$it_id}' where id = ".intval($row['maxid']));
        }
    }
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_PRODUCT_UPD) {
    // cn_product.php의 update_product 시그니처에 맞춰 전체 필드를 전달
    $ok = update_product(
        $id,
        $name,
        $type_code ?: 'ROOM',
        $description,
        is_null($base_amount) ? 0 : $base_amount,
        $period_type ?: 'MONTH',
        is_null($is_active) ? 1 : $is_active,
        is_null($sort_order) ? 0 : $sort_order
    );
    if ($ok && !is_null($it_id)) {
        sql_query("update cn_product set it_id = ".($it_id === '' ? "null" : "'{$it_id}'")." where id = {$id}");
    }
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_PRODUCT_DEL) {
    $ok = delete_product($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
