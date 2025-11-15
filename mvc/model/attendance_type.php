<?php

function select_attendance_type_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_attendance_type
            order by sort_order asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_attendance_type_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_attendance_type");
    return $row['cnt'];
}

function select_attendance_type_one($id) {
    return sql_fetch("select * from cn_attendance_type where id = {$id}");
}

function select_attendance_type_active($is_active=1, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_attendance_type
            where is_active = {$is_active}
            order by sort_order asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_attendance_type($mb_id, $name, $description=null, $is_active=1, $sort_order=0) {
    $desc_sql = is_null($description) ? "null" : "'{$description}'";
    $sql = "insert into cn_attendance_type
            set mb_id = '{$mb_id}',
                name = '{$name}',
                description = {$desc_sql},
                is_active = {$is_active},
                sort_order = {$sort_order}";
    return sql_query($sql);
}

function update_attendance_type($id, $name, $description=null, $is_active=1, $sort_order=0) {
    $desc_sql = is_null($description) ? "null" : "'{$description}'";
    $sql = "update cn_attendance_type
            set name = '{$name}',
                description = {$desc_sql},
                is_active = {$is_active},
                sort_order = {$sort_order}
            where id = {$id}";
    return sql_query($sql);
}

function delete_attendance_type($id) {
    return sql_query("delete from cn_attendance_type where id = {$id}");
}
?>
