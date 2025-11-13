<?php
/* cn_auth_code.php */

function select_auth_code_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_auth_code
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

function select_auth_code_one($id) {
    $sql = "select *
            from cn_auth_code
            where id = {$id}";
    return sql_fetch($sql);
}

function insert_auth_code($mb_id, $phone, $code, $expires_dt) {
    $sql = "insert into cn_auth_code
            set mb_id = '{$mb_id}',
                phone = '{$phone}',
                code = '{$code}',
                expires_dt = '{$expires_dt}'";
    return sql_query($sql);
}

function update_auth_code_used($id, $used=1) {
    $sql = "update cn_auth_code
            set used = {$used}
            where id = {$id}";
    return sql_query($sql);
}

function delete_auth_code($id) {
    $sql = "delete from cn_auth_code
            where id = {$id}";
    return sql_query($sql);
}
?>
