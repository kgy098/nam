<?php
include_once('./_common.php');

$type = $_REQUEST['type'] ?? '';

switch ($type) {

  case 'STUDY_REPORT_LIST':
    $page = max(1, (int)($_REQUEST['page'] ?? 1));
    $rows = max(1, min(200, (int)($_REQUEST['rows'] ?? 20)));
    $start = ($page - 1) * $rows;

    $mb_id = trim($_REQUEST['mb_id'] ?? '');
    $class = trim($_REQUEST['class'] ?? '');
    $date_from = trim($_REQUEST['date_from'] ?? '');
    $date_to = trim($_REQUEST['date_to'] ?? '');
    $keyword = trim($_REQUEST['keyword'] ?? '');

    $total = select_study_report_listcnt($mb_id, $class, $date_from, $date_to, $keyword);
    $list = select_study_report_list($start, $rows, $mb_id, $class, $date_from, $date_to, $keyword);
    foreach ($list as &$row) {

      $file = get_board_file('cn_study_report', $row['id'], 0); // bf_no = 0 기준 파일

      if ($file && $file['bf_file']) {
        $row['result_image'] = G5_DATA_URL . "/file/cn_study_report/" . $file['bf_file'];
      } else {
        $row['result_image'] = null;
      }
    }
    unset($row);


    jres(true, ['total' => $total, 'list' => $list, 'page' => $page, 'rows' => $rows]);
    break;

  case 'STUDY_REPORT_MY_LIST':
    $page = max(1, (int)($_REQUEST['page'] ?? 1));
    $rows = max(1, min(200, (int)($_REQUEST['rows'] ?? 20)));
    $start = ($page - 1) * $rows;

    $mb_id = $member['mb_id']; // 로그인한 학생 고정

    $subject_id = trim($_REQUEST['subject_id'] ?? '');
    $date_from  = trim($_REQUEST['date_from'] ?? '');
    $date_to    = trim($_REQUEST['date_to'] ?? '');

    // 학생 전용 함수 사용
    $total = select_study_report_listcnt_app($mb_id, $subject_id, $date_from, $date_to);
    $list  = select_study_report_list_app($mb_id, $start, $rows, $subject_id, $date_from, $date_to);

    jres(true, [
      'total' => $total,
      'list'  => $list,
      'page'  => $page,
      'rows'  => $rows
    ]);
    break;

  case 'STUDY_REPORT_GET':
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = select_study_report_one($id);
    if (!$row) jres(false, 'not found');

    jres(true, $row);
    break;

  case 'STUDY_REPORT_CREATE':
    $mb_id = trim($_REQUEST['mb_id'] ?? '');
    $subject = trim($_REQUEST['subject'] ?? '');
    $title = trim($_REQUEST['title'] ?? '');
    $content = trim($_REQUEST['content'] ?? '');
    $report_date = trim($_REQUEST['report_date'] ?? date('Y-m-d'));

    if ($mb_id === '' || $title === '') jres(false, 'required');

    // mb_id가 실제 존재하는지 확인
    $target = sql_fetch("SELECT mb_id FROM g5_member WHERE mb_id='{$mb_id}'");
    if (!$target) jres(false, 'member not found');

    $reg_id = $member['mb_id'];   // 로그인 사용자
    $ok = insert_study_report($mb_id, $subject_id, $title, $content, $report_date, $reg_id);
    if (!$ok) jres(false, 'insert fail');

    // 방금 생성된 데이터 조회
    $new = sql_fetch("SELECT r.*, m.mb_name, m.class 
                      FROM cn_study_report r
                      LEFT JOIN g5_member m ON r.mb_id = m.mb_id
                      ORDER BY r.id DESC LIMIT 1");

    jres(true, $new);
    break;

  case 'STUDY_REPORT_UPDATE':
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    // 기존 데이터 조회
    $old = select_study_report_one($id);
    if (!$old) jres(false, 'not found');

    // 수정할 값들 (입력 없으면 기존값 유지)
    $subject = isset($_REQUEST['subject']) ? trim($_REQUEST['subject']) : $old['subject'];
    $title = isset($_REQUEST['title']) ? trim($_REQUEST['title']) : $old['title'];
    $content = isset($_REQUEST['content']) ? trim($_REQUEST['content']) : $old['content'];
    $report_date = isset($_REQUEST['report_date']) ? trim($_REQUEST['report_date']) : $old['report_date'];

    $ok = update_study_report($id, $mb_id, $subject_id, $title, $content, $report_date);
    if (!$ok) jres(false, 'update fail');

    $row = select_study_report_one($id);
    jres(true, $row);
    break;

  case 'STUDY_REPORT_DELETE':
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $login_id = $member['mb_id'];
    $ok = delete_study_report($id, $login_id);
    if (!$ok) jres(false, 'delete fail');

    jres(true, 'deleted');
    break;

  case 'STUDY_REPORT_FILE_UPLOAD':
    $wr_id = (int)($_REQUEST['wr_id'] ?? 0);
    if ($wr_id <= 0) jres(false, 'invalid wr_id');

    // 학습보고서 존재 여부 확인
    $report = select_study_report_one($wr_id);
    if (!$report) jres(false, 'report not found');

    // 업로드 디렉토리 설정
    $upload_dir = G5_DATA_PATH . '/file/' . '/cn_study_report';

    // 허용 확장자
    $allowed_ext = ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'hwp', 'zip'];

    // 최대 파일 크기 (10MB)
    $max_size = 10 * 1024 * 1024;

    $result = file_upload('file', $upload_dir, $allowed_ext, $max_size);

    elog(print_r($_FILES, true));
    if ($result === false) {
      jres(false, 'file upload fail');
    }

    // 다음 bf_no 구하기
    $max_no = sql_fetch("SELECT IFNULL(MAX(bf_no), -1) as max_no 
                         FROM g5_board_file 
                         WHERE bo_table='cn_study_report' AND wr_id={$wr_id}");
    $bf_no = $max_no['max_no'] + 1;

    // g5_board_file에 저장
    $file_data = [
      'bo_table'   => 'cn_study_report',
      'wr_id'      => $wr_id,
      'bf_no'      => $bf_no,
      'bf_source'  => $result['bf_source'],
      'bf_file'    => $result['bf_file'],
      'bf_filesize' => $result['bf_filesize'],
      'bf_width'   => $result['bf_width'],
      'bf_height'  => $result['bf_height'],
      'bf_type'    => in_array($result['bf_ext'], ['jpg', 'jpeg', 'png', 'gif']) ? 1 : 0
    ];

    $ok = insert_board_file($file_data);
    if (!$ok) {
      // DB 저장 실패 시 업로드된 파일 삭제
      @unlink($result['bf_path']);
      jres(false, 'file db insert fail');
    }

    // 상대 경로로 변환
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
    break;

  case 'STUDY_REPORT_FILE_DELETE':
    $wr_id = (int)($_REQUEST['wr_id'] ?? 0);
    $bf_no = (int)($_REQUEST['bf_no'] ?? 0);

    if ($wr_id <= 0 || $bf_no < 0) jres(false, 'invalid params');

    // 파일 정보 조회
    $file = get_board_file('cn_study_report', $wr_id, $bf_no);
    if (!$file) jres(false, 'file not found');

    // 실제 파일 삭제
    $file_path = G5_DATA_PATH . '/study_report/' . $file['bf_file'];
    if (file_exists($file_path)) {
      @unlink($file_path);
    }

    // DB에서 삭제
    $ok = delete_board_file('cn_study_report', $wr_id, $bf_no);
    if (!$ok) jres(false, 'file delete fail');

    jres(true, 'file deleted');
    break;

  case 'STUDY_REPORT_FILE_LIST':
    $wr_id = (int)($_REQUEST['wr_id'] ?? 0);
    if ($wr_id <= 0) jres(false, 'invalid wr_id');

    $files = get_board_file_list('cn_study_report', $wr_id);

    jres(true, ['files' => $files]);
    break;

  default:
    jres(false, 'invalid type');
}
