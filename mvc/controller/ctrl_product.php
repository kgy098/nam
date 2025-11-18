<?php
include_once('./_common.php');

$type = $_REQUEST['type'] ?? '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num'])   ? intval($_REQUEST['num'])   : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id           = $_REQUEST['id'] ?? 0;
$mb_id        = $_REQUEST['mb_id'] ?? null;
$it_id        = array_key_exists('it_id', $_REQUEST) ? $_REQUEST['it_id'] : null;
$name         = $_REQUEST['name'] ?? null;
$type_code    = $_REQUEST['type_code'] ?? null;
$description  = array_key_exists('description', $_REQUEST) ? $_REQUEST['description'] : null;
$base_amount  = ($_REQUEST['base_amount'] ?? '') !== '' ? intval($_REQUEST['base_amount']) : null;
$period_type  = $_REQUEST['period_type'] ?? null;
$is_active    = ($_REQUEST['is_active'] ?? '') !== '' ? intval($_REQUEST['is_active']) : null;
$sort_order   = ($_REQUEST['sort_order'] ?? '') !== '' ? intval($_REQUEST['sort_order']) : null;


/* LIST */
if ($type === AJAX_PRODUCT_LIST) {

    $params = [];
    if ($type_code) $params['type'] = $type_code;
    if (!is_null($is_active)) $params['active'] = $is_active;
    if (!empty($name)) $params['name'] = $name;

    $list = select_product_list($start, $num, $params);
    echo json_encode($list ? ['result'=>'SUCCESS','data'=>$list] : ['result'=>'FAIL']);
    exit;
}

/* GET */
else if ($type === AJAX_PRODUCT_GET) {

    $row = select_product_one($id);
    echo json_encode($row ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);
    exit;
}

/* ADD */
else if ($type === AJAX_PRODUCT_ADD) {

    $new_id = insert_product(
        $mb_id,
        $name,
        $type_code ?: 'ROOM',
        $description,
        $base_amount ?? 0,
        $period_type ?: 'MONTH',
        1,                    // is_active = 1 기본값
        $sort_order ?? 0
    );

    if ($new_id && $it_id !== null) {
        update_product_it_id($new_id, $it_id);
    }

    echo json_encode($new_id ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);
    exit;
}

/* UPDATE */
else if ($type === AJAX_PRODUCT_UPD) {

    $fields = [
        'name'        => $name,
        'type'        => $type_code,
        'description' => $description,
        'base_amount' => $base_amount,
        'period_type' => $period_type,
        'is_active'   => $is_active,
        'sort_order'  => $sort_order
    ];

    $ok = update_product($id, $fields);

    if ($ok && !is_null($it_id)) {
        update_product_it_id($id, $it_id);
    }

    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);
    exit;
}

/* DELETE → soft delete */
else if ($type === AJAX_PRODUCT_DEL) {

    $ok = soft_delete_product($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);
    exit;
}

/* DEFAULT */
else {
    echo json_encode(['result'=>'FAIL']);
    exit;
}
?>
