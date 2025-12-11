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

  echo json_encode(['result' => 'SUCCESS', 'data' => ['list' => $list, 'total' => $cnt]]);
} else if ($type === AJAX_NOTICE_GET) {

  $row = select_notice_one($id);

  if ($row) {

    // 게시판 파일 정보 가져오기
    $bo_table = 'notice';   // ← 실제 notice 테이블명 맞는지 확인 (g5_board에서 사용되는 bo_table)
    $file = get_board_file($bo_table, $id, 0); // 첫 번째 파일
    // elog( print_r($file, true) );

    if ($file && $file['bf_file']) {

      // 실제 다운로드 가능한 URL
      $file_url = G5_DATA_URL . '/file/' . $bo_table . '/' . $file['bf_file'];

      $row['file_url']  = $file_url;
      $row['file_name'] = $file['bf_source'];
    } else {
      $row['file_url']  = null;
      $row['file_name'] = null;
    }

    // elog( print_r($row, true) );
    echo json_encode(['result' => 'SUCCESS', 'data' => $row]);
  } else {
    echo json_encode(['result' => 'FAIL']);
  }

  // echo json_encode($row ? ['result'=>'SUCCESS','data'=>$row] : ['result'=>'FAIL']);

} else if ($type === AJAX_NOTICE_ADD) {

  $ok = insert_notice($mb_id, $writer_name, $title, $content, $file_path, $file_name);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else if ($type === AJAX_NOTICE_UPD) {

  $ok = update_notice($id, $writer_name, $title, $content, $file_path, $file_name);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else if ($type === AJAX_NOTICE_DEL) {

  $ok = delete_notice($id);
  echo json_encode($ok ? ['result' => 'SUCCESS'] : ['result' => 'FAIL']);
} else {
  echo json_encode(['result' => 'FAIL']);
}
