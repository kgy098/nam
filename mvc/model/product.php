<?php
/* cn_product.php */

function select_product_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_product
            order by sort_order asc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }
    return $list;
}

function select_product_one($id) {
    $sql = "select *
            from cn_product
            where id = {$id}";
    return sql_fetch($sql);
}

function insert_product($mb_id, $name, $type, $description, $base_amount, $period_type, $is_active=1, $sort_order=0) {
    $sql = "insert into cn_product
            set mb_id = '{$mb_id}',
                name = '{$name}',
                type = '{$type}',
                description = '{$description}',
                base_amount = {$base_amount},
                period_type = '{$period_type}',
                is_active = {$is_active},
                sort_order = {$sort_order}";
    return sql_query($sql);
}

function update_product($id, $name, $type, $description, $base_amount, $period_type, $is_active, $sort_order) {
    $sql = "update cn_product
            set name = '{$name}',
                type = '{$type}',
                description = '{$description}',
                base_amount = {$base_amount},
                period_type = '{$period_type}',
                is_active = {$is_active},
                sort_order = {$sort_order}
            where id = {$id}";
    return sql_query($sql);
}

function delete_product($id) {
    $sql = "delete from cn_product
            where id = {$id}";
    return sql_query($sql);
}
?>
