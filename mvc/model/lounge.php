<?php
/* cn_lounge.php */

function select_lounge_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_lounge
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_lounge_listcnt() {
    $sql = "select count(id) as cnt from cn_lounge";
    $row = sql_fetch($sql);
    return $row['cnt'];
}

function select_lounge_one($id) {
    $sql = "select * from cn_lounge where id = {$id}";
    return sql_fetch($sql);
}

function select_lounge_active($is_active=1, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_lounge
            where is_active = {$is_active}
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_lounge($name, $location, $total_seats, $is_active=1) {
    $sql = "insert into cn_lounge
            set name = '{$name}',
                location = '{$location}',
                total_seats = {$total_seats},
                is_active = {$is_active}";
    return sql_query($sql);
}

function update_lounge($id, $name, $location, $total_seats, $is_active) {
    $sql = "update cn_lounge
            set name = '{$name}',
                location = '{$location}',
                total_seats = {$total_seats},
                is_active = {$is_active}
            where id = {$id}";
    return sql_query($sql);
}

function delete_lounge($id) {
    $sql = "delete from cn_lounge where id = {$id}";
    return sql_query($sql);
}
?>
