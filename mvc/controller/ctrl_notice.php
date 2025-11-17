<?php
include_once('./_common.php');

$type = $_REQUEST['type'] ?? '';

$start = isset($_REQUEST['start']) ? (int)$_REQUEST['start'] : 0;
$num   = isset($_REQUEST['num']) ? (int)$_REQUEST['num'] : (defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20);

$id          = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
$mb_id       = $_REQUEST['mb_id'] ?? null;
$writer_name = $_REQUEST['writer_name'] ?? null;
$title       = $_REQUEST['title'] ?? null;
$content     = $_REQUEST['content'] ?? null;

$file_path   = array_key_exists('file_path', $_REQUEST) ? $_REQUEST['file_path'] : null;
$file_name   = array_key_exists('file_name', $_REQUEST) ? $_REQUEST['file_name'] : null;

if ($type === AJAX_NOTICE_LIST) {

    $list = select_notice_list($start, $num);
    $cnt  = select_notice_listcnt();

    echo json_encode(['result'=>'SUCCESS','data'=>['list'=>$list,'total'=>$cnt]]);

} else if ($type === AJAX_NOTICE_GET) {

    $row = select_notice_one($id);
    echo json_encode($row ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_NOTICE_ADD) {

    $ok = insert_notice($mb_id, $writer_name, $title, $content, $file_path, $file_name);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_NOTICE_UPD) {

    $ok = update_notice($id, $writer_name, $title, $content, $file_path, $file_name);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else if ($type === AJAX_NOTICE_DEL) {

    $ok = delete_notice($id);
    echo json_encode($ok ? ['result'=>'SUCCESS'] : ['result'=>'FAIL']);

} else {
    echo json_encode(['result'=>'FAIL']);
}
