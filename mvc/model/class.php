<?php

function select_class_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_class
            order by is_active desc, name asc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_class_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_class");
    return $row['cnt'];
}

function select_class_one($id) {
    return sql_fetch("select * from cn_class where id = {$id}");
}

function select_class_active($is_active=1, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_class
            where is_active = {$is_active}
            order by name asc, id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_class($name, $description=null, $is_active=1) {
    $desc_sql = is_null($description) ? "null" : "'{$description}'";
    $sql = "insert into cn_class
            set name = '{$name}',
                description = {$desc_sql},
                is_active = {$is_active}";
    return sql_query($sql);
}

function update_class($id, $name=null, $description=null, $is_active=null) {
    $sets = [];
    if (!is_null($name))        $sets[] = "name = '{$name}'";
    if (!is_null($description)) $sets[] = "description = '{$description}'";
    if (!is_null($is_active))   $sets[] = "is_active = {$is_active}";
    if (empty($sets)) return true;
    $sql = "update cn_class set ".implode(',', $sets)." where id = {$id}";
    return sql_query($sql);
}

function delete_class($id) {
    return sql_query("delete from cn_class where id = {$id}");
}
?>
