<?php
include_once('./_common.php');

$w = $_POST['w'] ?? '';
$id = (int)($_POST['id'] ?? 0);
$bo_table = $_POST['bo_table'] ?? 'cn_study_report';

$mb_id = $_POST['mb_id'] ?? '';
$title = $_POST['title'] ?? '';
$content = $_POST['content'] ?? '';
$report_date = date('Y-m-d');

// 파일 삭제 여부
$file_del = $_POST['file_del'] ?? '';

function json_response($success, $message = '') {
  echo json_encode([
    'result' => $success ? 'SUCCESS' : 'FAIL',
    'message' => $message
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

// 삭제
if ($w === 'd') {
  if ($id <= 0) json_response(false, 'ID가 유효하지 않습니다.');
  
  // 파일 삭제
  $file = get_board_file($bo_table, $id, 0);
  if ($file && !empty($file['bf_file'])) {
    $file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $file['bf_file'];
    if (file_exists($file_path)) {
      @unlink($file_path);
    }
  }
  delete_board_file_all($bo_table, $id);
  
  // 보고서 삭제
  $result = delete_study_report($id);
  json_response($result, $result ? '' : '삭제 실패');
}

// 등록/수정
if (empty($mb_id) || empty($title)) {
  json_response(false, '필수 항목을 입력해주세요.');
}

// 등록
if ($w === '' ) {
  $result = insert_study_report($mb_id, '', $title, $content, $report_date);
  
  if (!$result) {
    json_response(false, '등록 실패');
  }
  
  // 새로 생성된 ID 가져오기
  $new_id = sql_insert_id();
  
  // 파일 업로드
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    $upload_dir = G5_DATA_PATH . '/file/' . $bo_table;
    $file_result = file_upload('file', $upload_dir);
    
    if ($file_result) {
      insert_board_file([
        'bo_table' => $bo_table,
        'wr_id' => $new_id,
        'bf_no' => 0,
        'bf_source' => $file_result['bf_source'],
        'bf_file' => $file_result['bf_file'],
        'bf_filesize' => $file_result['bf_filesize'],
        'bf_width' => $file_result['bf_width'],
        'bf_height' => $file_result['bf_height'],
        'bf_type' => in_array($file_result['bf_ext'], ['jpg','jpeg','png','gif']) ? 1 : 0
      ]);
    }
  }
  
  json_response(true);
}

// 수정
if ($w === 'u') {
  if ($id <= 0) json_response(false, 'ID가 유효하지 않습니다.');
  
  $result = update_study_report($id, '', $title, $content, $report_date);
  
  if (!$result) {
    json_response(false, '수정 실패');
  }
  
  // 파일 삭제 체크
  if ($file_del === '1') {
    $file = get_board_file($bo_table, $id, 0);
    if ($file && !empty($file['bf_file'])) {
      $file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $file['bf_file'];
      if (file_exists($file_path)) {
        @unlink($file_path);
      }
      delete_board_file($bo_table, $id, 0);
    }
  }
  
  // 새 파일 업로드
  if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
    // 기존 파일 삭제
    $old_file = get_board_file($bo_table, $id, 0);
    if ($old_file && !empty($old_file['bf_file'])) {
      $file_path = G5_DATA_PATH . '/file/' . $bo_table . '/' . $old_file['bf_file'];
      if (file_exists($file_path)) {
        @unlink($file_path);
      }
      delete_board_file($bo_table, $id, 0);
    }
    
    // 새 파일 업로드
    $upload_dir = G5_DATA_PATH . '/file/' . $bo_table;
    $file_result = file_upload('file', $upload_dir);
    
    if ($file_result) {
      insert_board_file([
        'bo_table' => $bo_table,
        'wr_id' => $id,
        'bf_no' => 0,
        'bf_source' => $file_result['bf_source'],
        'bf_file' => $file_result['bf_file'],
        'bf_filesize' => $file_result['bf_filesize'],
        'bf_width' => $file_result['bf_width'],
        'bf_height' => $file_result['bf_height'],
        'bf_type' => in_array($file_result['bf_ext'], ['jpg','jpeg','png','gif']) ? 1 : 0
      ]);
    }
  }
  
  json_response(true);
}

json_response(false, '잘못된 요청입니다.');
?>