<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

/**********************************************************************
 * Q&A 컨트롤러
 * 규칙:
 * - if / else if 구조 사용
 * - Esc() 사용 안함
 * - CRUD 함수 호출 방식
 * - cn_qna 스키마 100% 일치
 * - type 상수 없이 문자 그대로 사용
 **********************************************************************/

$type = $_REQUEST['type'] ?? '';

/* QNA_LIST
 * 파라미터:
 *  - page
 *  - rows
 *  - student_mb_id (선택)
 *  - teacher_mb_id (선택)
 *  - status (선택)
 *  - keyword (선택) → title, question 검색
 */
if ($type === 'QNA_LIST') {

  $page = max(1, (int)($_REQUEST['page'] ?? 1));
  $rows = max(1, min(200, (int)($_REQUEST['rows'] ?? 20)));
  $offset = ($page - 1) * $rows;

  $student_mb_id = trim($_REQUEST['student_mb_id'] ?? '');
  $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
  $status        = trim($_REQUEST['status'] ?? '');
  $keyword       = trim($_REQUEST['keyword'] ?? '');

  $list = select_qna_list($student_mb_id, $teacher_mb_id, $status, $keyword, $start = 0, $num = CN_PAGE_NUM);
  $total = select_qna_listcnt($student_mb_id, $teacher_mb_id, $status, $keyword);

  jres(true, [
    'total' => $total,
    'list'  => $list,
    'page'  => $page,
    'rows'  => $rows
  ]);
}


/* QNA_GET
 * 단건 조회
 */ else if ($type === 'QNA_GET') {

  $id = (int)($_REQUEST['id'] ?? 0);
  if ($id <= 0) jres(false, 'invalid id');

  $row = select_qna_one($id);
  if (!$row) jres(false, 'not found');

  // ================================
  // 첨부파일 조회 추가
  // ================================
  $files = [];
  $sql = "
      SELECT bf_no, bf_source, bf_file, bf_filesize, bf_width, bf_height, bf_type
      FROM g5_board_file
      WHERE bo_table = 'qna' AND wr_id = {$id}
      ORDER BY bf_no ASC
  ";
  $res = sql_query($sql);
  for ($i = 0; $file = sql_fetch_array($res); $i++) {

    // 웹에서 접근 가능한 URL 생성
    $file_url = G5_DATA_URL . '/file/qna/' . $file['bf_file'];

    $files[] = [
      'bf_no'     => $file['bf_no'],
      'file_name' => $file['bf_source'],
      'file_url'  => $file_url,
      'file_size' => $file['bf_filesize'],
      'is_image'  => (int)$file['bf_type'] === 1
    ];
  }
  $row['files'] = $files;

  jres(true, $row);
}


/* QNA_CREATE
 * 질문 등록
 * 파라미터:
 *  - student_mb_id
 *  - title
 *  - question
 *  - teacher_mb_id (optional)
 */ else if ($type === 'QNA_CREATE') {

  $student_mb_id = trim($_REQUEST['student_mb_id'] ?? '');
  $title         = trim($_REQUEST['title'] ?? '');
  $question      = trim($_REQUEST['question'] ?? '');
  $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');

  if ($student_mb_id === '' || $title === '' || $question === '') {
    jres(false, 'required');
  }

  $teacher_val = ($teacher_mb_id === '') ? null : $teacher_mb_id;

  $ok = insert_qna_question(
    $student_mb_id,
    $title,
    $question,
    $teacher_val
  );

  if (!$ok) jres(false, 'insert fail');

  // 마지막 insert row 반환
  $new = sql_fetch("SELECT * FROM cn_qna ORDER BY id DESC LIMIT 1");
  jres(true, $new);
}


/* QNA_UPDATE
 * 질문 수정
 * 파라미터:
 *  - id
 *  - title
 *  - question
 *  - teacher_mb_id (optional)
 *  - status (optional)
 */ else if ($type === 'QNA_UPDATE') {

  $id = (int)($_REQUEST['id'] ?? 0);
  if ($id <= 0) jres(false, 'invalid id');

  $title         = trim($_REQUEST['title'] ?? '');
  $question      = trim($_REQUEST['question'] ?? '');
  $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
  $status        = trim($_REQUEST['status'] ?? '');

  $teacher_val = ($teacher_mb_id === '') ? null : $teacher_mb_id;
  $status_val  = ($status === '') ? null : $status;

  $ok = update_qna_question(
    $id,
    $title,
    $question,
    $teacher_val,
    $status_val
  );

  if (!$ok) jres(false, 'update fail');

  $row = select_qna_one($id);
  jres(true, $row);
}


/* QNA_ANSWER
 * 선생님 답변 등록
 * 파라미터:
 *  - id
 *  - teacher_mb_id
 *  - answer
 *  - answered_dt (optional, 없으면 NOW)
 */ else if ($type === 'QNA_ANSWER') {

  $id            = (int)($_REQUEST['id'] ?? 0);
  $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
  $answer        = trim($_REQUEST['answer'] ?? '');
  $answered_dt   = trim($_REQUEST['answered_dt'] ?? '');

  if ($id <= 0 || $teacher_mb_id === '' || $answer === '') {
    jres(false, 'required');
  }

  // answered_dt 처리
  // (빈 문자열이면 NULL)
  $answered_val = ($answered_dt === '') ? null : $answered_dt;

  $ok = answer_qna($id, $teacher_mb_id, $answer, $answered_val);

  if (!$ok) jres(false, 'answer fail');

  $row = select_qna_one($id);
  jres(true, $row);
}


else if ($type === 'QNA_ANSWER_FILE_UPLOAD') {

  $id            = (int)($_REQUEST['id'] ?? 0);
  $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
  $answer        = trim($_REQUEST['answer'] ?? '');
  $answered_dt   = trim($_REQUEST['answered_dt'] ?? '');
  $ok2 = answer_qna($id, $teacher_mb_id, $answer, $answered_val);


  $qna_id = (int)($_REQUEST['id'] ?? 0);
  if ($qna_id <= 0) jres(false, 'invalid qna_id');

  // QNA 데이터 확인
  $row = select_qna_one($qna_id);
  if (!$row) jres(false, 'qna not found');

  // 업로드 경로
  $upload_dir = G5_DATA_PATH . '/file/qna';

  // 허용 확장자
  $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'hwp', 'zip'];

  // 10MB
  $max_size = 10 * 1024 * 1024;

  // 업로드 처리
  $result = file_upload('file', $upload_dir, $allowed_ext, $max_size);
  if ($result === false) jres(false, 'file upload fail');

  // bf_no 계산
  $max_no = sql_fetch("
        SELECT IFNULL(MAX(bf_no), -1) as max_no
        FROM g5_board_file 
        WHERE bo_table='qna' AND wr_id={$qna_id}
    ");
  $bf_no = $max_no['max_no'] + 1;

  // g5_board_file 저장
  $file_data = [
    'bo_table'    => 'qna',
    'wr_id'       => $qna_id,
    'bf_no'       => $bf_no,
    'bf_source'   => $result['bf_source'],
    'bf_file'     => $result['bf_file'],
    'bf_filesize' => $result['bf_filesize'],
    'bf_width'    => $result['bf_width'],
    'bf_height'   => $result['bf_height'],
    'bf_type'     => in_array($result['bf_ext'], ['jpg', 'jpeg', 'png', 'gif']) ? 1 : 0
  ];

  $ok = insert_board_file($file_data);

  if (!$ok) {
    @unlink($result['bf_path']);
    jres(false, 'file db insert fail');
  }

  // 상대 URL 반환
  $web_path = str_replace(G5_PATH, '', $result['bf_path']);

  jres(true, [
    'bf_no'       => $bf_no,
    'file_name'   => $result['bf_source'],
    'saved_name'  => $result['bf_file'],
    'file_size'   => $result['bf_filesize'],
    'file_ext'    => $result['bf_ext'],
    'file_path'   => $result['bf_path'],
    'web_path'    => $web_path
  ]);
}

/* QNA_DELETE */ 
else if ($type === 'QNA_DELETE') {

  $id = (int)($_REQUEST['id'] ?? 0);
  if ($id <= 0) jres(false, 'invalid id');

  $ok = delete_qna($id);
  if (!$ok) jres(false, 'delete fail');

  jres(true, 'deleted');
} 

else if ($type === 'QNA_ANSWER_FILE_DELETE') {

    $id    = (int)($_POST['id'] ?? 0);
    $bf_no = (int)($_POST['bf_no'] ?? 0);

    if ($id <= 0) jres(false, 'invalid id');
    if ($bf_no < 0) jres(false, 'invalid bf_no');

    // 파일 조회
    $file = sql_fetch("
        SELECT * FROM g5_board_file
        WHERE bo_table = 'qna'
          AND wr_id = {$id}
          AND bf_no = {$bf_no}
        LIMIT 1
    ");

    if (!$file) jres(false, 'file not found');

    // 실제 파일 삭제
    $filepath = G5_DATA_PATH . '/file/qna/' . $file['bf_file'];
    if (file_exists($filepath)) {
        @unlink($filepath);
    }

    // DB 삭제
    sql_query("
        DELETE FROM g5_board_file
        WHERE bo_table = 'qna'
          AND wr_id = {$id}
          AND bf_no = {$bf_no}
    ");

    jres(true, 'deleted');
}


/* invalid type */ 
else {
  jres(false, 'invalid type');
}
