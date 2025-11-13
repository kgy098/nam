<?php
/* cn_lounge_reservation.php */

function select_lounge_reservation_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_lounge_reservation
            order by reserved_date desc, start_time desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_lounge_reservation_listcnt() {
    $sql = "select count(id) as cnt from cn_lounge_reservation";
    $row = sql_fetch($sql);
    return $row['cnt'];
}

function select_lounge_reservation_one($id) {
    $sql = "select * from cn_lounge_reservation where id = {$id}";
    return sql_fetch($sql);
}

function select_lounge_reservation_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_lounge_reservation
            where mb_id = '{$mb_id}'
            order by reserved_date desc, start_time desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_lounge_reservation_by_date($reserved_date, $lounge_id=null, $seat_id=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "reserved_date = '{$reserved_date}'";
    if (!is_null($lounge_id)) $where .= " and lounge_id = {$lounge_id}";
    if (!is_null($seat_id))   $where .= " and seat_id = {$seat_id}";
    $sql = "select *
            from cn_lounge_reservation
            where {$where}
            order by start_time asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_lounge_reservation($mb_id, $lounge_id, $seat_id, $reserved_date, $start_time, $end_time, $status='예약') {
    $sql = "insert into cn_lounge_reservation
            set mb_id = '{$mb_id}',
                lounge_id = {$lounge_id},
                seat_id = {$seat_id},
                reserved_date = '{$reserved_date}',
                start_time = '{$start_time}',
                end_time = '{$end_time}',
                status = '{$status}'";
    return sql_query($sql);
}

function update_lounge_reservation($id, $lounge_id, $seat_id, $reserved_date, $start_time, $end_time, $status) {
    $sql = "update cn_lounge_reservation
            set lounge_id = {$lounge_id},
                seat_id = {$seat_id},
                reserved_date = '{$reserved_date}',
                start_time = '{$start_time}',
                end_time = '{$end_time}',
                status = '{$status}'
            where id = {$id}";
    return sql_query($sql);
}

function delete_lounge_reservation($id) {
    $sql = "delete from cn_lounge_reservation where id = {$id}";
    return sql_query($sql);
}
?>
