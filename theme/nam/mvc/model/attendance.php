<?php

function select_attendance_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_attendance
            order by attend_dt desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_attendance_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_attendance");
    return $row['cnt'];
}

function select_attendance_one($id) {
    return sql_fetch("select * from cn_attendance where id = {$id}");
}

function select_attendance_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_attendance
            where mb_id = '{$mb_id}'
            order by attend_dt desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_attendance_between($from_dt, $to_dt, $mb_id=null, $type=null, $status=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "attend_dt between '{$from_dt}' and '{$to_dt}'";
    if (!is_null($mb_id))   $where .= " and mb_id = '{$mb_id}'";
    if (!is_null($type))    $where .= " and type = '{$type}'";
    if (!is_null($status))  $where .= " and status = '{$status}'";
    $sql = "select * from cn_attendance
            where {$where}
            order by attend_dt desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_attendance($mb_id, $attend_type_id=null, $attend_dt, $type='입실', $status='출석') {
    $type_id_sql = is_null($attend_type_id) ? "null" : intval($attend_type_id);
    $sql = "insert into cn_attendance
            set mb_id = '{$mb_id}',
                attend_type_id = {$type_id_sql},
                attend_dt = '{$attend_dt}',
                type = '{$type}',
                status = '{$status}'";
    return sql_query($sql);
}

function update_attendance($id, $attend_type_id=null, $attend_dt=null, $type=null, $status=null) {
    $sets = [];
    if (!is_null($attend_type_id)) $sets[] = "attend_type_id = " . intval($attend_type_id);
    if (!is_null($attend_dt))      $sets[] = "attend_dt = '{$attend_dt}'";
    if (!is_null($type))           $sets[] = "type = '{$type}'";
    if (!is_null($status))         $sets[] = "status = '{$status}'";
    if (empty($sets)) return true;
    $sql = "update cn_attendance set ".implode(',', $sets)." where id = {$id}";
    return sql_query($sql);
}

function delete_attendance($id) {
    return sql_query("delete from cn_attendance where id = {$id}");
}
?>
