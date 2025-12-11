<?php
include_once('./_common.php');

$w        = $_POST['w'] ?? '';
$id       = (int)($_POST['id'] ?? 0);
$bo_table = $_POST['bo_table'] ?? 'qna';

$student_mb_id = $_POST['student_mb_id'] ?? '';
$title         = $_POST['title'] ?? '';
$question      = $_POST['question'] ?? '';
$teacher_mb_id = $_POST['teacher_mb_id'] ?? '';

$file_del   = $_POST['file_del'] ?? '';

/* --------------------------------------------
 * JSON 응답 함수
 * -------------------------------------------- */
function json_response($success, $message = '')
{
  echo json_encode([
    'result'  => $success ? 'SUCCESS' : 'FAIL',
    'message' => $message
  ], JSON_UNESCAPED_UNICODE);
  exit;
}

/* --------------------------------------------
 * 삭제 (질문 + 파일)
 * -------------------------------------------- */
if ($w === 'd') {

  if ($id <= 0)
    json_response(false, 'ID가 유효하지 않습니다.');

  // 기존 파일 삭제
  $file = get_board_file($bo_table, $id, 0);
  if ($file && !empty($file['bf_file'])) {

    $path = G5_DATA_PATH . "/file/{$bo_table}/" . $file['bf_file'];

    if (file_exists($path)) {
      @unlink($path);
    }
  }

  delete_board_file_all($bo_table, $id);

  // 질문 삭제
  $ok = delete_qna($id);

  json_response($ok, $ok ? '' : '삭제 실패');
}



/* --------------------------------------------
 * 등록
 * -------------------------------------------- */
if ($w === '') {

  $ok = insert_qna_question(
    $student_mb_id,
    $title,
    $question,
    $teacher_mb_id ?: null
  );

  if (!$ok) {
    json_response(false, '등록 실패');
  }

  // insert된 id 값
  $new_id = sql_insert_id();

  /* ================================
     * 새 파일 업로드 가능
     * ================================ */
  if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

    $upload_dir = G5_DATA_PATH . "/file/{$bo_table}";
    @mkdir($upload_dir, G5_DIR_PERMISSION, true);

    $f = file_upload('file', $upload_dir);

    if ($f) {
      insert_board_file([
        'bo_table'    => $bo_table,
        'wr_id'       => $new_id,
        'bf_no'       => 0,
        'bf_source'   => $f['bf_source'],
        'bf_file'     => $f['bf_file'],
        'bf_filesize' => $f['bf_filesize'],
        'bf_width'    => $f['bf_width'],
        'bf_height'   => $f['bf_height'],
        'bf_type'     => in_array($f['bf_ext'], ['jpg', 'jpeg', 'png', 'gif']) ? 1 : 0
      ]);
    }
  }

  json_response(true);
}


/* --------------------------------------------
 * 수정(질문 내용 + 파일 첨부)
 * -------------------------------------------- */
if ($w === 'u') {

  if ($id <= 0)
    json_response(false, 'ID가 유효하지 않습니다.');

  $ok = answer_qna($id, $member['mb_id'], $answer, '');

  if (!$ok) json_response(false, '수정 실패');


  /* --------------------------
     * 파일 삭제
     * -------------------------- */
  if ($file_del === '1') {

    $file = get_board_file($bo_table, $id, 0);

    if ($file && !empty($file['bf_file'])) {

      $path = G5_DATA_PATH . "/file/{$bo_table}/" . $file['bf_file'];

      if (file_exists($path)) {
        @unlink($path);
      }

      delete_board_file($bo_table, $id, 0);
    }
  }


  /* --------------------------
     * 새 파일 업로드
     * -------------------------- */
  if (!empty($_FILES['file']['name']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {

    // 기존 파일 제거
    $old = get_board_file($bo_table, $id, 0);

    if ($old && !empty($old['bf_file'])) {

      $path = G5_DATA_PATH . "/file/{$bo_table}/" . $old['bf_file'];

      if (file_exists($path)) {
        @unlink($path);
      }

      delete_board_file($bo_table, $id, 0);
    }

    // 새 파일 업로드
    $upload_dir = G5_DATA_PATH . "/file/{$bo_table}";
    @mkdir($upload_dir, G5_DIR_PERMISSION, true);

    $f = file_upload('file', $upload_dir);

    if ($f) {
      insert_board_file([
        'bo_table'    => $bo_table,
        'wr_id'       => $id,
        'bf_no'       => 0,
        'bf_source'   => $f['bf_source'],
        'bf_file'     => $f['bf_file'],
        'bf_filesize' => $f['bf_filesize'],
        'bf_width'    => $f['bf_width'],
        'bf_height'   => $f['bf_height'],
        'bf_type'     => in_array($f['bf_ext'], ['jpg', 'jpeg', 'png', 'gif']) ? 1 : 0
      ]);
    }
  }

  json_response(true);
}

json_response(false, '잘못된 요청입니다.');
