<?php
include_once('./_common.php');

// error_log(__FILE__ . __LINE__ . "\n _REQUEST1: " . print_r($_REQUEST, true));

if (!$is_admin) {
  alert('접근 권한이 없습니다.');
  exit;
}

$w       = $_POST['w'] ?? '';      // '' 신규, 'u' 수정, 'd' 삭제
$id      = intval($_POST['id'] ?? 0);
$title   = trim($_POST['title'] ?? '');
$content = $_POST['content'] ?? '';
$file_del = $_POST['file_del'] ?? '';

$mb_id       = $member['mb_id'];
$writer_name = $member['mb_name'];

$bo_table   = 'notice';
$upload_dir = G5_DATA_PATH . '/file/' . $bo_table;

// 필수 체크
if ( $w!='d' ) {
  if ($title === '') alert('제목을 입력하세요.');
  if ($content === '') alert('내용을 입력하세요.');
}


// ------------------------------------------------------
// 신규 등록
// ------------------------------------------------------
if ($w === '') {

  // cn_notice 저장
  $id = insert_notice_return_id($mb_id, $writer_name, $title, $content);

  // -----------------------------
  // 파일 업로드 처리
  // -----------------------------
  if (!empty($_FILES['file']['tmp_name'])) {

    $up = file_upload('file', $upload_dir);
    // error_log(__FILE__ . __LINE__ . "\n up: " . print_r($up, true));

    if ($up) {
      insert_board_file([
        'bo_table'    => $bo_table,
        'wr_id'       => $id,
        'bf_no'       => 0,
        'bf_source'   => $up['bf_source'],
        'bf_file'     => $up['bf_file'],
        'bf_filesize' => $up['bf_filesize'],
        'bf_width'    => $up['bf_width'],
        'bf_height'   => $up['bf_height'],
        'bf_type'     => 0
      ]);
    }
  }

  echo json_encode(['result' => 'SUCCESS', 'id' => $id]);
  exit;
}



// ------------------------------------------------------
// 수정
// ------------------------------------------------------
if ($w === 'u') {

  $row = sql_fetch("SELECT * FROM cn_notice WHERE id = {$id}");

  if (!$row) alert('존재하지 않는 게시글입니다.');

  // cn_notice 수정
  update_notice_fields($id, $writer_name, $title, $content);

  // ===============================
  // 기존 파일 삭제 요청
  // ===============================
  if ($file_del === '1') {

    $old = get_board_file($bo_table, $id, 0);
    if ($old && $old['bf_file']) {
      $path = $upload_dir . '/' . $old['bf_file'];
      if (file_exists($path)) @unlink($path);
    }

    delete_board_file($bo_table, $id, 0);
  }

  // ===============================
  // 새 파일 업로드
  // ===============================
  if (!empty($_FILES['file']['tmp_name'])) {

    // 기존 파일 삭제
    $old = get_board_file($bo_table, $id, 0);
    if ($old && $old['bf_file']) {
      $path = $upload_dir . '/' . $old['bf_file'];
      if (file_exists($path)) @unlink($path);
    }

    // 기존 DB 삭제
    delete_board_file($bo_table, $id, 0);

    // 업로드 실행
    $up = file_upload('file', $upload_dir);

    if ($up) {
      insert_board_file([
        'bo_table'    => $bo_table,
        'wr_id'       => $id,
        'bf_no'       => 0,
        'bf_source'   => $up['bf_source'],
        'bf_file'     => $up['bf_file'],
        'bf_filesize' => $up['bf_filesize'],
        'bf_width'    => $up['bf_width'],
        'bf_height'   => $up['bf_height'],
        'bf_type'     => 0
      ]);
    }
  }

  echo json_encode(['result' => 'SUCCESS', 'id' => $id]);
  exit;
} else if ($w === 'd') {
  // error_log(__FILE__ . __LINE__ . "\n _REQUEST: " . print_r($_REQUEST, true));
  if (!$id) {
    echo json_encode(['result' => 'FAIL', 'message' => '잘못된 게시글 번호입니다.']);
    exit;
  }

  // 게시글 존재 여부 확인
  $row = sql_fetch("SELECT * FROM cn_notice WHERE id = {$id}");
  if (!$row) {
    echo json_encode(['result' => 'FAIL', 'message' => '존재하지 않는 게시글입니다.']);
    exit;
  }

  // 첨부파일 목록 조회 (g5_board_file CRUD 사용)
  $files = get_board_file_list($bo_table, $id);

  // 실제 파일 삭제
  if ($files) {
    foreach ($files as $f) {
      if (!empty($f['bf_file'])) {
        $path = $upload_dir . '/' . $f['bf_file'];
        if (file_exists($path)) {
          @unlink($path);
        }
      }
    }
  }

  // g5_board_file 레코드 삭제
  delete_board_file_all($bo_table, $id);

  // cn_notice 레코드 삭제 (CRUD 함수가 있다면 그걸 사용)
  // 예: delete_notice($id);
  sql_query("DELETE FROM cn_notice WHERE id = {$id}");

  echo json_encode(['result' => 'SUCCESS', 'id' => $id]);
  exit;
}



// ------------------------------------------------------
// 잘못된 접근
// ------------------------------------------------------
alert('잘못된 접근입니다.');
