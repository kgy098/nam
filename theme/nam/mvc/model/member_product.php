<?php
/* cn_member_product.php */

function select_member_product_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_member_product
            order by checkin_datetime desc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

function select_member_product_one($id) {
    $sql = "select *
            from cn_member_product
            where id = {$id}";
    return sql_fetch($sql);
}

function insert_member_product($mb_id, $product_id, $checkin_datetime, $status='입실', $room_no='', $memo='') {
    $sql = "insert into cn_member_product
            set mb_id = '{$mb_id}',
                product_id = {$product_id},
                checkin_datetime = '{$checkin_datetime}',
                status = '{$status}',
                room_no = '{$room_no}',
                memo = '{$memo}'";
    return sql_query($sql);
}

function update_member_product($id, $checkout_datetime, $status, $room_no, $memo) {
    $sql = "update cn_member_product
            set checkout_datetime = '{$checkout_datetime}',
                status = '{$status}',
                room_no = '{$room_no}',
                memo = '{$memo}'
            where id = {$id}";
    return sql_query($sql);
}

function delete_member_product($id) {
    $sql = "delete from cn_member_product
            where id = {$id}";
    return sql_query($sql);
}
?>
