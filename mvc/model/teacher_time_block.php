<?php

function select_teacher_time_block_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_teacher_time_block
            order by target_date desc, start_time asc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_teacher_time_block_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_teacher_time_block");
    return $row['cnt'];
}

function select_teacher_time_block_one($id) {
    return sql_fetch("select * from cn_teacher_time_block where id = {$id}");
}

function select_teacher_time_block_by_teacher_date($mb_id, $target_date, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_teacher_time_block
            where mb_id = '{$mb_id}' and target_date = '{$target_date}'
            order by start_time asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_teacher_time_block_range($mb_id, $from_date, $to_date, $type=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "mb_id = '{$mb_id}' and target_date between '{$from_date}' and '{$to_date}'";
    if (!is_null($type)) $where .= " and type = '{$type}'";
    $sql = "select * from cn_teacher_time_block
            where {$where}
            order by target_date asc, start_time asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_teacher_time_block($mb_id, $target_date, $start_time, $end_time, $type, $memo='') {
    $sql = "insert into cn_teacher_time_block
            set mb_id = '{$mb_id}',
                target_date = '{$target_date}',
                start_time = '{$start_time}',
                end_time = '{$end_time}',
                type = '{$type}',
                memo = '{$memo}'";
    return sql_query($sql);
}

function update_teacher_time_block($id, $target_date, $start_time, $end_time, $type, $memo='') {
    $sql = "update cn_teacher_time_block
            set target_date = '{$target_date}',
                start_time = '{$start_time}',
                end_time = '{$end_time}',
                type = '{$type}',
                memo = '{$memo}'
            where id = {$id}";
    return sql_query($sql);
}

function delete_teacher_time_block($id) {
    return sql_query("delete from cn_teacher_time_block where id = {$id}");
}
?>
