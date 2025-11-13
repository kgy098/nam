<?php

function select_mock_result_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_result
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_result_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_mock_result");
    return $row['cnt'];
}

function select_mock_result_one($id) {
    return sql_fetch("select * from cn_mock_result where id = {$id}");
}

function select_mock_result_by_mock($mock_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_result
            where mock_id = {$mock_id}
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_result_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_result
            where mb_id = '{$mb_id}'
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_result_by_mock_student($mock_id, $mb_id) {
    $sql = "select * from cn_mock_result
            where mock_id = {$mock_id}
              and mb_id = '{$mb_id}'
            order by subject_id asc, id asc";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_mock_result($mock_id, $mb_id, $subject_id=null, $attended=1, $score=null) {
    $sub_sql = is_null($subject_id) ? "null" : intval($subject_id);
    $score_sql = is_null($score) ? "null" : intval($score);
    $sql = "insert into cn_mock_result
            set mock_id = {$mock_id},
                mb_id = '{$mb_id}',
                subject_id = {$sub_sql},
                attended = {$attended},
                score = {$score_sql}";
    return sql_query($sql);
}

function update_mock_result($id, $subject_id=null, $attended=1, $score=null) {
    $sub_sql = is_null($subject_id) ? "null" : intval($subject_id);
    $score_sql = is_null($score) ? "null" : intval($score);
    $sql = "update cn_mock_result
            set subject_id = {$sub_sql},
                attended = {$attended},
                score = {$score_sql}
            where id = {$id}";
    return sql_query($sql);
}

function delete_mock_result($id) {
    return sql_query("delete from cn_mock_result where id = {$id}");
}
?>
