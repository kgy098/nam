<?php
/* cn_schedule.php */

function select_schedule_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_schedule
            order by start_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_schedule_listcnt() {
    $sql = "select count(id) as cnt from cn_schedule";
    $row = sql_fetch($sql);
    return $row['cnt'];
}

function select_schedule_one($id) {
    $sql = "select * from cn_schedule where id = {$id}";
    return sql_fetch($sql);
}

function select_schedule_between($from_date, $to_date, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_schedule
            where start_date >= '{$from_date}'
              and (end_date is null or end_date <= '{$to_date}')
            order by start_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_schedule($mb_id, $title, $description, $start_date, $end_date=null) {
    $end = is_null($end_date) ? "null" : "'{$end_date}'";
    $sql = "insert into cn_schedule
            set mb_id = '{$mb_id}',
                title = '{$title}',
                description = '{$description}',
                start_date = '{$start_date}',
                end_date = {$end}";
    return sql_query($sql);
}

function update_schedule($id, $title, $description, $start_date, $end_date=null) {
    $end = is_null($end_date) ? "null" : "'{$end_date}'";
    $sql = "update cn_schedule
            set title = '{$title}',
                description = '{$description}',
                start_date = '{$start_date}',
                end_date = {$end}
            where id = {$id}";
    return sql_query($sql);
}

function delete_schedule($id) {
    $sql = "delete from cn_schedule where id = {$id}";
    return sql_query($sql);
}
?>
