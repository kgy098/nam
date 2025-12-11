<?php
/* cn_notice.php */


/* 목록 */
function select_notice_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from cn_notice
            order by id desc
            limit {$start}, {$num}";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_notice_listcnt() {
    $row = sql_fetch("select count(*) as cnt from cn_notice");
    return $row['cnt'];
}

/* 단일 */
function select_notice_one($id) {
    $sql = "select * from cn_notice where id = {$id}";
    return sql_fetch($sql);
}


/* ================================
   INSERT (ID 반환)
   ================================ */
function insert_notice_return_id($mb_id, $writer_name, $title, $content) {

    $sql = "insert into cn_notice
            set mb_id       = '".$mb_id."',
                writer_name = '".$writer_name."',
                title       = '".$title."',
                content     = '".$content."',
                reg_dt      = NOW()";

    sql_query($sql);

    return sql_insert_id();   // PK 반환
}


/* ================================
   UPDATE (필드 전달)
   ================================ */
function update_notice_fields($id, $writer_name, $title, $content) {

    $sql = "update cn_notice
            set writer_name = '".$writer_name."',
                title       = '".$title."',
                content     = '".$content."',
                mod_dt      = NOW()
            where id = {$id}";
    // elog($sql);
    return sql_query($sql);
}


/* 삭제 */
function delete_notice($id) {
    $sql = "delete from cn_notice where id = {$id}";
    return sql_query($sql);
}
?>
