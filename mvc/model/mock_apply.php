<?php

function select_mock_apply_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_apply
            order by applied_at desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_apply_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_mock_apply");
    return $row['cnt'];
}

function select_mock_apply_one($id) {
    return sql_fetch("select * from cn_mock_apply where id = {$id}");
}

function select_mock_apply_by_mock($mock_id, $status=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "mock_id = {$mock_id}";
    if (!is_null($status)) $where .= " and status = '{$status}'";
    $sql = "select * from cn_mock_apply
            where {$where}
            order by applied_at desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_apply_by_student($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_apply
            where mb_id = '{$mb_id}'
            order by applied_at desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function count_mock_apply_by_mock_status($mock_id, $status='신청') {
    $row = sql_fetch("select count(id) as cnt
                      from cn_mock_apply
                      where mock_id = {$mock_id} and status = '{$status}'");
    return $row['cnt'];
}

function insert_mock_apply($mock_id, $mb_id, $status='신청') {
    $sql = "insert into cn_mock_apply
            set mock_id = {$mock_id},
                mb_id = '{$mb_id}',
                status = '{$status}'";
    return sql_query($sql);
}

function update_mock_apply($id, $status) {
    $sql = "update cn_mock_apply
            set status = '{$status}'
            where id = {$id}";
    return sql_query($sql);
}

function delete_mock_apply($id) {
    return sql_query("delete from cn_mock_apply where id = {$id}");
}
?>
