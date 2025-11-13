<?php

function select_notify_log_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_notify_log
            order by sent_at desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_notify_log_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_notify_log");
    return $row['cnt'];
}

function select_notify_log_one($id) {
    return sql_fetch("select * from cn_notify_log where id = {$id}");
}

function select_notify_log_by_target($target_mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_notify_log
            where target_mb_id = '{$target_mb_id}'
            order by sent_at desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_notify_log_between($from_dt, $to_dt, $target_mb_id=null, $channel=null, $status=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "sent_at between '{$from_dt}' and '{$to_dt}'";
    if (!is_null($target_mb_id)) $where .= " and target_mb_id = '{$target_mb_id}'";
    if (!is_null($channel))      $where .= " and channel = '{$channel}'";
    if (!is_null($status))       $where .= " and status = '{$status}'";
    $sql = "select * from cn_notify_log
            where {$where}
            order by sent_at desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_notify_log($target_mb_id, $message, $channel, $sent_at, $status='성공', $error_message=null) {
    $err_sql = is_null($error_message) ? "null" : "'{$error_message}'";
    $sql = "insert into cn_notify_log
            set target_mb_id = '{$target_mb_id}',
                message = '{$message}',
                channel = '{$channel}',
                sent_at = '{$sent_at}',
                status = '{$status}',
                error_message = {$err_sql}";
    return sql_query($sql);
}

function update_notify_log($id, $status=null, $error_message=null) {
    $sets = [];
    if (!is_null($status))        $sets[] = "status = '{$status}'";
    if (!is_null($error_message)) $sets[] = "error_message = '{$error_message}'";
    if (empty($sets)) return true;
    $sql = "update cn_notify_log set ".implode(',', $sets)." where id = {$id}";
    return sql_query($sql);
}

function delete_notify_log($id) {
    return sql_query("delete from cn_notify_log where id = {$id}");
}
?>
