<?php
include_once('./_common.php');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

$start = isset($_REQUEST['start']) ? intval($_REQUEST['start']) : 0;
$num   = isset($_REQUEST['num']) ? intval($_REQUEST['num']) : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id   = isset($_REQUEST['id']) ? intval($_REQUEST['id']) : 0;
$name = isset($_REQUEST['name']) ? $_REQUEST['name'] : null;

// description: '' → null 로 통일
$description = array_key_exists('description', $_REQUEST)
  ? ($_REQUEST['description'] === '' ? null : $_REQUEST['description'])
  : null;

// is_active 기본값 1
$is_active = (isset($_REQUEST['is_active']) && $_REQUEST['is_active'] !== '')
  ? intval($_REQUEST['is_active'])
  : 1;

if ($type === AJAX_CLASS_LIST) {

  $list = select_class_list($start, $num);
  // error_log(__FILE__.__LINE__."\n list: " . print_r($list, true));
  echo json_encode(!empty($list) ? ['result' => 'SUCCESS', 'data' => $list] : ['result' => 'FAIL']);
} else if ($type === AJAX_CLASS_GET) {

  $row = select_class_one($id);
  echo json_encode(!empty($row) ? ['result' => 'SUCCESS', 'data' => $row] : ['result' => 'FAIL']);
} else if ($type === AJAX_CLASS_ACTIVE) {

  $list = select_class_active($is_active, $start, $num);
  echo json_encode(!empty($list) ? ['result' => 'SUCCESS', 'data' => $list] : ['result' => 'FAIL']);
} else if ($type === AJAX_CLASS_ADD) {

  $ok = insert_class($name, $description, $is_active);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else if ($type === AJAX_CLASS_UPD) {

  $ok = update_class($id, $name, $description, $is_active);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else if ($type === AJAX_CLASS_ACTIVE_UPD) {

    // is_active 값만 변경 (soft delete / 복구 용도)
    $ok = update_class($id, null, null, $is_active);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);
} else if ($type === AJAX_CLASS_DEL) {

  $ok = delete_class($id);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else {

  echo json_encode(['result' => 'FAIL']);
}
