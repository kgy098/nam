<?php

function select_video_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_video
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    error_log(__FILE__.__LINE__."\n SQL: " . $sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_video_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_video");
    return $row['cnt'];
}

function select_video_one($id) {
    return sql_fetch("select * from cn_video where id = {$id}");
}

function select_video_by_uploader($mb_id, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_video
            where mb_id = '{$mb_id}'
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_video_by_class($class_name, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_video
            where class_name = '{$class_name}'
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_video($title, $youtube_id, $description='', $class_name=null, $mb_id=null) {
    $class_sql = is_null($class_name) ? "null" : "'{$class_name}'";
    $mb_sql = is_null($mb_id) ? "null" : "'{$mb_id}'";
    $sql = "insert into cn_video
            set title = '{$title}',
                description = '{$description}',
                youtube_id = '{$youtube_id}',
                class_name = {$class_sql},
                mb_id = {$mb_sql}";
    return sql_query($sql);
}

function update_video($id, $title, $youtube_id, $description='', $class_name=null) {
    $class_sql = is_null($class_name) ? "null" : "'{$class_name}'";
    $sql = "update cn_video
            set title = '{$title}',
                description = '{$description}',
                youtube_id = '{$youtube_id}',
                class_name = {$class_sql}
            where id = {$id}";
    return sql_query($sql);
}

function increase_video_views($id, $step=1) {
    $sql = "update cn_video
            set views = views + {$step}
            where id = {$id}";
    return sql_query($sql);
}

function delete_video($id) {
    return sql_query("delete from cn_video where id = {$id}");
}
?>
