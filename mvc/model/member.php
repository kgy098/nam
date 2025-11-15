<?php

function select_member_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select mb_no, mb_id, mb_name, role, auth_no, mb_hp, mb_email, mb_datetime
            from g5_member
            order by mb_no desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_member_listcnt() {
    $row = sql_fetch("select count(mb_no) as cnt from g5_member");
    return $row['cnt'];
}

function select_member_one($mb_no) {
    return sql_fetch("select * from g5_member where mb_no = {$mb_no}");
}

function select_member_by_role($role, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select mb_no, mb_id, mb_name, role, auth_no, mb_hp, mb_email, mb_datetime
            from g5_member
            where role = '{$role}'
            order by mb_no desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_member($mb_id, $mb_name, $role, $auth_no=null, $mb_hp=null, $mb_email=null) {
    $auth_sql = is_null($auth_no) ? "null" : "'{$auth_no}'";
    $hp_sql = is_null($mb_hp) ? "null" : "'{$mb_hp}'";
    $email_sql = is_null($mb_email) ? "null" : "'{$mb_email}'";
    $sql = "insert into g5_member
            set mb_id = '{$mb_id}',
                mb_name = '{$mb_name}',
                role = '{$role}',
                auth_no = {$auth_sql},
                mb_hp = {$hp_sql},
                mb_email = {$email_sql},
                mb_datetime = now()";
    return sql_query($sql);
}

function update_member($mb_no, $mb_name, $role, $auth_no=null, $mb_hp=null, $mb_email=null) {
    $auth_sql = is_null($auth_no) ? "null" : "'{$auth_no}'";
    $hp_sql = is_null($mb_hp) ? "null" : "'{$mb_hp}'";
    $email_sql = is_null($mb_email) ? "null" : "'{$mb_email}'";
    $sql = "update g5_member
            set mb_name = '{$mb_name}',
                role = '{$role}',
                auth_no = {$auth_sql},
                mb_hp = {$hp_sql},
                mb_email = {$email_sql}
            where mb_no = {$mb_no}";
    return sql_query($sql);
}

function delete_member($mb_no) {
    return sql_query("delete from g5_member where mb_no = {$mb_no}");
}

?>
