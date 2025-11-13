<?php

function select_mock_subject_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_subject
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_subject_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_mock_subject");
    return $row['cnt'];
}

function select_mock_subject_one($id) {
    return sql_fetch("select * from cn_mock_subject where id = {$id}");
}

function select_mock_subject_by_mock($mock_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_subject
            where mock_id = {$mock_id}
            order by id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_mock_subject($mock_id, $subject_name) {
    $sql = "insert into cn_mock_subject
            set mock_id = {$mock_id},
                subject_name = '{$subject_name}'";
    return sql_query($sql);
}

function update_mock_subject($id, $subject_name) {
    $sql = "update cn_mock_subject
            set subject_name = '{$subject_name}'
            where id = {$id}";
    return sql_query($sql);
}

function delete_mock_subject($id) {
    return sql_query("delete from cn_mock_subject where id = {$id}");
}
?>
