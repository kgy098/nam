<?php

function select_lounge_seat_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_lounge_seat
            order by lounge_id asc, seat_no asc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_lounge_seat_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_lounge_seat");
    return $row['cnt'];
}

function select_lounge_seat_one($id) {
    return sql_fetch("select * from cn_lounge_seat where id = {$id}");
}

function select_lounge_seat_by_lounge($lounge_id, $only_active=false, $start=0, $num=CN_PAGE_NUM) {
    $where = "lounge_id = {$lounge_id}";
    if ($only_active) $where .= " and is_active = 1";
    $sql = "select * from cn_lounge_seat
            where {$where}
            order by seat_no asc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_lounge_seat($lounge_id, $seat_no, $is_active=1) {
    $sql = "insert into cn_lounge_seat
            set lounge_id = {$lounge_id},
                seat_no = '{$seat_no}',
                is_active = {$is_active}";
    return sql_query($sql);
}

function update_lounge_seat($id, $lounge_id, $seat_no, $is_active) {
    $sql = "update cn_lounge_seat
            set lounge_id = {$lounge_id},
                seat_no = '{$seat_no}',
                is_active = {$is_active}
            where id = {$id}";
    return sql_query($sql);
}

function delete_lounge_seat($id) {
    return sql_query("delete from cn_lounge_seat where id = {$id}");
}
?>
