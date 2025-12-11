<?php

function select_study_report_list($start = 0, $num = CN_PAGE_NUM, $mb_id = '', $class = '', $date_from = '', $date_to = '', $keyword = '')
{
  $where = "1";
  if ($mb_id !== '')     $where .= " AND r.mb_id = '{$mb_id}'";
  if ($class !== '')     $where .= " AND m.class = '{$class}'";
  if ($date_from !== '') $where .= " AND r.report_date >= '{$date_from}'";
  if ($date_to !== '')   $where .= " AND r.report_date <= '{$date_to}'";
  if ($keyword !== '') {
    $where .= " AND (r.title LIKE '%{$keyword}%' 
                     OR r.content LIKE '%{$keyword}%' 
                     OR m.mb_name LIKE '%{$keyword}%')";
  }

  $sql = "
        SELECT 
            r.*,
            r.reg_id,  
            m.mb_name,
            m.class,
            ms.type,
            ms.subject_name
        FROM cn_study_report r
        LEFT JOIN g5_member m ON r.mb_id = m.mb_id
        LEFT JOIN cn_mock_subject ms ON r.subject_id = ms.id
        WHERE {$where}
        ORDER BY r.report_date DESC, r.id DESC
        LIMIT {$start}, {$num}
    ";

  $result = sql_query($sql);
  $list = [];

  while ($row = sql_fetch_array($result)) {

    // 첨부파일 개수
    $file_cnt = sql_fetch("SELECT COUNT(*) AS cnt 
                               FROM g5_board_file 
                               WHERE bo_table='cn_study_report' 
                               AND wr_id={$row['id']}");

    $row['file_count'] = $file_cnt['cnt'];
    $list[] = $row;
  }

  return $list;
}



function select_study_report_listcnt($mb_id = '', $class = '', $date_from = '', $date_to = '', $keyword = '')
{
  $where = "1";
  if ($mb_id !== '')     $where .= " AND r.mb_id = '{$mb_id}'";
  if ($class !== '')     $where .= " AND m.class = '{$class}'";
  if ($date_from !== '') $where .= " AND r.report_date >= '{$date_from}'";
  if ($date_to !== '')   $where .= " AND r.report_date <= '{$date_to}'";
  if ($keyword !== '') {
    $where .= " AND (r.title LIKE '%{$keyword}%' 
                     OR r.content LIKE '%{$keyword}%' 
                     OR m.mb_name LIKE '%{$keyword}%')";
  }

  $row = sql_fetch("
        SELECT COUNT(r.id) AS cnt
        FROM cn_study_report r
        LEFT JOIN g5_member m ON r.mb_id = m.mb_id
        LEFT JOIN cn_mock_subject ms ON r.subject_id = ms.id
        WHERE {$where}
    ");

  return $row['cnt'];
}

function select_study_report_list_app($mb_id, $start = 0, $rows = 20, $subject_id = '', $date_from = '', $date_to = '')
{
  $where = "r.mb_id = '{$mb_id}'";

  if ($subject_id !== '') $where .= " AND r.subject_id = '{$subject_id}'";
  if ($date_from !== '')  $where .= " AND r.report_date >= '{$date_from}'";
  if ($date_to !== '')    $where .= " AND r.report_date <= '{$date_to}'";

  $sql = "
        SELECT 
            r.*,
            r.reg_id, 
            ms.type,
            ms.subject_name
        FROM cn_study_report r
        LEFT JOIN cn_mock_subject ms ON r.subject_id = ms.id
        WHERE {$where}
        ORDER BY r.report_date DESC, r.id DESC
        LIMIT {$start}, {$rows}
    ";
  elog("\n SQL: " . $sql);
  $result = sql_query($sql);
  $list = [];

  while ($row = sql_fetch_array($result)) {

    // 첫 번째 첨부파일 가져오기
    $file = get_board_file('cn_study_report', $row['id'], 0);

    if ($file && $file['bf_file']) {
      $row['result_image'] = G5_DATA_URL . "/file/cn_study_report/" . $file['bf_file'];
      $row['file_name'] = $file['bf_source'];
    } else {
      $row['result_image'] = null;
      $row['file_name'] = null;
    }

    $list[] = $row;
  }

  return $list;
}
function select_study_report_listcnt_app($mb_id, $subject_id = '', $date_from = '', $date_to = '')
{
  $where = "r.mb_id = '{$mb_id}'";

  if ($subject_id !== '') {
    $where .= " AND r.subject_id = '{$subject_id}'";
  }
  if ($date_from !== '') {
    $where .= " AND r.report_date >= '{$date_from}'";
  }
  if ($date_to !== '') {
    $where .= " AND r.report_date <= '{$date_to}'";
  }

  $sql = "
        SELECT COUNT(r.id) AS cnt
        FROM cn_study_report r
        LEFT JOIN cn_mock_subject ms ON r.subject_id = ms.id
        WHERE {$where}
    ";

  $row = sql_fetch($sql);
  return (int)$row['cnt'];
}


function select_study_report_one($id)
{
  $sql = "SELECT r.*, m.mb_name, m.class 
            FROM cn_study_report r
            LEFT JOIN g5_member m ON r.mb_id = m.mb_id
            WHERE r.id = " . intval($id);
  $row = sql_fetch($sql);

  if ($row) {
    // 첨부파일 리스트 추가
    $row['files'] = get_board_file_list('cn_study_report', $row['id']);
  }

  return $row;
}

function select_study_report_by_student($mb_id, $start = 0, $num = CN_PAGE_NUM)
{
  $sql = "SELECT r.*, m.mb_name, m.class 
            FROM cn_study_report r
            LEFT JOIN g5_member m ON r.mb_id = m.mb_id
            WHERE r.mb_id = '{$mb_id}'
            ORDER BY r.report_date DESC, r.id DESC
            LIMIT $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) {
    // 첨부파일 개수 추가
    $file_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table='cn_study_report' AND wr_id={$row['id']}");
    $row['file_count'] = $file_cnt['cnt'];
    $list[] = $row;
  }
  return $list;
}

function select_study_report_between($from_date, $to_date, $mb_id = null, $start = 0, $num = CN_PAGE_NUM)
{
  $where = "r.report_date BETWEEN '{$from_date}' AND '{$to_date}'";
  if (!is_null($mb_id)) {
    $where .= " AND r.mb_id = '{$mb_id}'";
  }
  $sql = "SELECT r.*, m.mb_name, m.class 
            FROM cn_study_report r
            LEFT JOIN g5_member m ON r.mb_id = m.mb_id
            WHERE {$where}
            ORDER BY r.report_date DESC, r.id DESC
            LIMIT $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) {
    // 첨부파일 개수 추가
    $file_cnt = sql_fetch("SELECT COUNT(*) as cnt FROM g5_board_file WHERE bo_table='cn_study_report' AND wr_id={$row['id']}");
    $row['file_count'] = $file_cnt['cnt'];
    $list[] = $row;
  }
  return $list;
}

function insert_study_report($mb_id, $subject_id, $title, $content, $report_date, $reg_id)
{
  $sql = "INSERT INTO cn_study_report
            SET mb_id = '{$mb_id}',
                subject_id = '{$subject_id}',
                title = '{$title}',
                content = '{$content}',
                report_date = '{$report_date}',
                reg_id = '{$reg_id}',
                reg_dt = NOW(),
                mod_dt = NOW()";
  return sql_query($sql);
}

function update_study_report($id, $mb_id, $subject_id, $title, $content, $report_date)
{
  $id = intval($id);
  $mb_id = sql_real_escape_string($mb_id);

  $sql = "
    UPDATE cn_study_report
       SET mb_id = '{$mb_id}',
           subject_id = '{$subject_id}',
           title = '{$title}',
           content = '{$content}',
           report_date = '{$report_date}',
           mod_dt = NOW()
     WHERE id = {$id}
  ";
  return sql_query($sql);
}

function delete_study_report($id, $login_id)
{
  $id = intval($id);
  $login_id = sql_real_escape_string($login_id);

  // 본인 작성 여부 체크
  $row = sql_fetch("SELECT reg_id FROM cn_study_report WHERE id = {$id}");
  if (!$row || $row['reg_id'] != $login_id) {
    return false;  // 권한 없음
  }

  // 첨부파일 삭제
  $files = get_board_file_list('cn_study_report', $id);
  foreach ($files as $file) {
    $path = G5_DATA_PATH . '/file/cn_study_report/' . $file['bf_file'];
    if (file_exists($path)) @unlink($path);
  }

  delete_board_file_all('cn_study_report', $id);

  return sql_query("DELETE FROM cn_study_report WHERE id = {$id}");
}


function delete_study_report_adm($id)
{
  $id = intval($id);

  // 첨부파일 삭제
  $files = get_board_file_list('cn_study_report', $id);
  foreach ($files as $file) {
    $path = G5_DATA_PATH . '/file/cn_study_report/' . $file['bf_file'];
    if (file_exists($path)) @unlink($path);
  }

  delete_board_file_all('cn_study_report', $id);

  return sql_query("DELETE FROM cn_study_report WHERE id = {$id}");
}
