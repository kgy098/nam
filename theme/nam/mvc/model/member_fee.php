<?php

function select_member_fee_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_member_fee
            order by due_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_member_fee_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_member_fee");
    return $row['cnt'];
}

function select_member_fee_one($id) {
    return sql_fetch("select * from cn_member_fee where id = {$id}");
}

function select_member_fee_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_member_fee
            where mb_id = '{$mb_id}'
            order by due_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_member_fee_by_status($status, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_member_fee
            where status = '{$status}'
            order by due_date asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_member_fee_due_between($from_date, $to_date, $mb_id=null, $status=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "due_date between '{$from_date}' and '{$to_date}'";
    if (!is_null($mb_id))  $where .= " and mb_id = '{$mb_id}'";
    if (!is_null($status)) $where .= " and status = '{$status}'";
    $sql = "select * from cn_member_fee
            where {$where}
            order by due_date asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_member_fee($mb_id, $it_id, $due_date, $amount, $status='미납') {
    $sql = "insert into cn_member_fee
            set mb_id = '{$mb_id}',
                it_id = '{$it_id}',
                due_date = '{$due_date}',
                amount = {$amount},
                status = '{$status}'";
    return sql_query($sql);
}

function update_member_fee($id, $it_id=null, $due_date=null, $amount=null, $status=null) {
    $sets = [];
    if (!is_null($it_id))    $sets[] = "it_id = '{$it_id}'";
    if (!is_null($due_date)) $sets[] = "due_date = '{$due_date}'";
    if (!is_null($amount))   $sets[] = "amount = " . intval($amount);
    if (!is_null($status))   $sets[] = "status = '{$status}'";
    if (empty($sets)) return true;
    $sql = "update cn_member_fee set ".implode(',', $sets)." where id = {$id}";
    return sql_query($sql);
}

function delete_member_fee($id) {
    return sql_query("delete from cn_member_fee where id = {$id}");
}
?>
