<?php

function select_attendance_type_list($start=0, $num=CN_PAGE_NUM) {
    $start = (int)$start;
    $num   = (int)$num;
    if ($start < 0) $start = 0;
    if ($num <= 0)  $num = CN_PAGE_NUM;

    $sql = "select *
            from cn_attendance_type
            order by sort_order asc, id asc
            limit {$start}, {$num}";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_attendance_type_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_attendance_type");
    return (int)$row['cnt'];
}

function select_attendance_type_one($id) {
    $id = (int)$id;
    return sql_fetch("select * from cn_attendance_type where id = {$id}");
}

function select_attendance_type_active($is_active=1, $start=0, $num=CN_PAGE_NUM) {
    $is_active = (int)$is_active;
    $start     = (int)$start;
    $num       = (int)$num;
    if ($start < 0) $start = 0;
    if ($num <= 0)  $num = CN_PAGE_NUM;

    $sql = "select *
            from cn_attendance_type
            where is_active = {$is_active}
            order by sort_order asc, id asc
            limit {$start}, {$num}";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_attendance_type($mb_id, $name, $description=null, $is_active=1, $sort_order=0) {
    $is_active  = (int)$is_active;
    $sort_order = (int)$sort_order;
    $desc_sql   = is_null($description) ? "null" : "'{$description}'";

    $sql = "insert into cn_attendance_type
            set mb_id      = '{$mb_id}',
                name        = '{$name}',
                description = {$desc_sql},
                is_active   = {$is_active},
                sort_order  = {$sort_order}";
    error_log(__FILE__.__LINE__."\n SQL: " . $sql);    

    return sql_query($sql);
}

/* 선택필드 업데이트 버전 */
function update_attendance_type($id, $name=null, $description=null, $is_active=null, $sort_order=null) {
    $id = (int)$id;
    $sets = [];

    if (!is_null($name)) {
        $sets[] = "name = '{$name}'";
    }
    if (!is_null($description)) {
        $desc = $description;
        $sets[] = "description = '{$desc}'";
    }
    if (!is_null($is_active)) {
        $is_active = (int)$is_active;
        $sets[] = "is_active = {$is_active}";
    }
    if (!is_null($sort_order)) {
        $sort_order = (int)$sort_order;
        $sets[] = "sort_order = {$sort_order}";
    }

    if (empty($sets)) return true;

    $sql = "update cn_attendance_type
            set ".implode(', ', $sets)."
            where id = {$id}";
    return sql_query($sql);
}

function delete_attendance_type($id) {
    $id = (int)$id;
    return sql_query("delete from cn_attendance_type where id = {$id}");
}
?>
