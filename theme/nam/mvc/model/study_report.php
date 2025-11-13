<?php

function select_study_report_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_study_report
            order by report_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_study_report_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_study_report");
    return $row['cnt'];
}

function select_study_report_one($id) {
    return sql_fetch("select * from cn_study_report where id = {$id}");
}

function select_study_report_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_study_report
            where mb_id = '{$mb_id}'
            order by report_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_study_report_between($from_date, $to_date, $mb_id=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "report_date between '{$from_date}' and '{$to_date}'";
    if (!is_null($mb_id)) $where .= " and mb_id = '{$mb_id}'";
    $sql = "select * from cn_study_report
            where {$where}
            order by report_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_study_report($mb_id, $subject, $title, $content, $report_date, $file_path=null) {
    $file_sql = is_null($file_path) ? "null" : "'{$file_path}'";
    $sql = "insert into cn_study_report
            set mb_id = '{$mb_id}',
                subject = '{$subject}',
                title = '{$title}',
                content = '{$content}',
                report_date = '{$report_date}',
                file_path = {$file_sql}";
    return sql_query($sql);
}

function update_study_report($id, $subject, $title, $content, $report_date, $file_path=null) {
    $file_sql = is_null($file_path) ? "null" : "'{$file_path}'";
    $sql = "update cn_study_report
            set subject = '{$subject}',
                title = '{$title}',
                content = '{$content}',
                report_date = '{$report_date}',
                file_path = {$file_sql}
            where id = {$id}";
    return sql_query($sql);
}

function delete_study_report($id) {
    return sql_query("delete from cn_study_report where id = {$id}");
}
?>
