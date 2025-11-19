<?php

function select_study_report_list($start=0, $num=CN_PAGE_NUM, $mb_id='', $class='', $date_from='', $date_to='', $keyword='') {
    $where = "1";
    if($mb_id !== '')     $where .= " AND r.mb_id = '{$mb_id}'";
    if($class !== '')     $where .= " AND m.class = '{$class}'";
    if($date_from !== '') $where .= " AND r.report_date >= '{$date_from}'";
    if($date_to !== '')   $where .= " AND r.report_date <= '{$date_to}'";
    if($keyword !== '') {
        $where .= " AND (r.title LIKE '%{$keyword}%' OR r.content LIKE '%{$keyword}%' OR m.mb_name LIKE '%{$keyword}%')";
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

function select_study_report_listcnt($mb_id='', $class='', $date_from='', $date_to='', $keyword='') {
    $where = "1";
    if($mb_id !== '')     $where .= " AND r.mb_id = '{$mb_id}'";
    if($class !== '')     $where .= " AND m.class = '{$class}'";
    if($date_from !== '') $where .= " AND r.report_date >= '{$date_from}'";
    if($date_to !== '')   $where .= " AND r.report_date <= '{$date_to}'";
    if($keyword !== '') {
        $where .= " AND (r.title LIKE '%{$keyword}%' OR r.content LIKE '%{$keyword}%' OR m.mb_name LIKE '%{$keyword}%')";
    }
    
    $row = sql_fetch("SELECT COUNT(r.id) as cnt 
                      FROM cn_study_report r
                      LEFT JOIN g5_member m ON r.mb_id = m.mb_id
                      WHERE {$where}");
    return $row['cnt'];
}

function select_study_report_one($id) {
    $sql = "SELECT r.*, m.mb_name, m.class 
            FROM cn_study_report r
            LEFT JOIN g5_member m ON r.mb_id = m.mb_id
            WHERE r.id = ".intval($id);
    $row = sql_fetch($sql);
    
    if($row) {
        // 첨부파일 리스트 추가
        $row['files'] = get_board_file_list('cn_study_report', $row['id']);
    }
    
    return $row;
}

function select_study_report_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
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

function select_study_report_between($from_date, $to_date, $mb_id=null, $start=0, $num=CN_PAGE_NUM) {
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

function insert_study_report($mb_id, $subject, $title, $content, $report_date) {
    $sql = "INSERT INTO cn_study_report
            SET mb_id = '{$mb_id}',
                subject = '{$subject}',
                title = '{$title}',
                content = '{$content}',
                report_date = '{$report_date}',
                reg_dt = NOW(),
                mod_dt = NOW()";
    return sql_query($sql);
}

function update_study_report($id, $subject, $title, $content, $report_date) {
    $id = intval($id);
    
    $sql = "UPDATE cn_study_report
            SET subject = '{$subject}',
                title = '{$title}',
                content = '{$content}',
                report_date = '{$report_date}',
                mod_dt = NOW()
            WHERE id = {$id}";
    return sql_query($sql);
}

function delete_study_report($id) {
    $id = intval($id);
    
    // 첨부파일 삭제 (실제 파일 + DB)
    $files = get_board_file_list('cn_study_report', $id);
    foreach($files as $file) {
        // 실제 파일 삭제
        $file_path = G5_DATA_PATH.'/study_report/'.$file['bf_file'];
        if(file_exists($file_path)) {
            @unlink($file_path);
        }
    }
    
    // DB에서 파일 정보 삭제
    delete_board_file_all('cn_study_report', $id);
    
    // 학습보고서 삭제
    return sql_query("DELETE FROM cn_study_report WHERE id = {$id}");
}

?>