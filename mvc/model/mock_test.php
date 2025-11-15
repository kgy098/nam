<?php

function select_mock_test_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_test
            order by exam_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_test_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_mock_test");
    return $row['cnt'];
}

function select_mock_test_one($id) {
    return sql_fetch("select * from cn_mock_test where id = {$id}");
}

function select_mock_test_by_status($status, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_test
            where status = '{$status}'
            order by exam_date desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_mock_test_between_exam($from_date, $to_date, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_mock_test
            where exam_date between '{$from_date}' and '{$to_date}'
            order by exam_date asc, id asc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_mock_test($name, $description, $apply_start, $apply_end, $exam_date, $status='접수중') {
    $sql = "insert into cn_mock_test
            set name = '{$name}',
                description = '{$description}',
                apply_start = '{$apply_start}',
                apply_end = '{$apply_end}',
                exam_date = '{$exam_date}',
                status = '{$status}'";
    return sql_query($sql);
}

function update_mock_test($id, $name, $description, $apply_start, $apply_end, $exam_date, $status) {
    $sql = "update cn_mock_test
            set name = '{$name}',
                description = '{$description}',
                apply_start = '{$apply_start}',
                apply_end = '{$apply_end}',
                exam_date = '{$exam_date}',
                status = '{$status}'
            where id = {$id}";
    return sql_query($sql);
}

function delete_mock_test($id) {
    return sql_query("delete from cn_mock_test where id = {$id}");
}
?>
