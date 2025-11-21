<?php
/* cn_lounge.php */

/* ---------------------------------------
   1) 라운지 리스트
--------------------------------------- */
function select_lounge_list($start=0, $num=CN_PAGE_NUM) {
    $start = (int)$start;
    $num   = (int)$num;

    $sql = "select *
            from cn_lounge
            WHERE is_active = 1
            order by id desc
            limit {$start}, {$num}";
    $result = sql_query($sql);

    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

/* ---------------------------------------
   2) 라운지 전체 카운트
--------------------------------------- */
function select_lounge_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_lounge");
    return (int)$row['cnt'];
}

/* ---------------------------------------
   3) 단건 조회
--------------------------------------- */
function select_lounge_one($id) {
    $id = (int)$id;

    $sql = "select * from cn_lounge where id = {$id}";
    return sql_fetch($sql);
}

/* ---------------------------------------
   4) is_active로 목록 조회
--------------------------------------- */
function select_lounge_active($is_active=1, $start=0, $num=CN_PAGE_NUM) {
    $is_active = (int)$is_active;
    $start = (int)$start;
    $num   = (int)$num;

    $sql = "select *
            from cn_lounge
            where is_active = {$is_active}
            order by id desc
            limit {$start}, {$num}";
    $result = sql_query($sql);

    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

/* ---------------------------------------
   5) 등록
--------------------------------------- */
function insert_lounge($name, $location, $total_seats, $is_active=1) {
    $name        = trim($name);
    $location    = trim($location);
    $total_seats = (int)$total_seats;
    $is_active   = (int)$is_active;

    $sql = "insert into cn_lounge
            set name        = '{$name}',
                location    = '{$location}',
                total_seats = {$total_seats},
                is_active   = {$is_active}";
    return sql_query($sql);
}

/* ---------------------------------------
   6) 수정
--------------------------------------- */
function update_lounge($id, $name, $location, $total_seats, $is_active) {
    $id          = (int)$id;
    $name        = trim($name);
    $location    = trim($location);
    $total_seats = (int)$total_seats;
    $is_active   = (int)$is_active;

    $sql = "update cn_lounge
            set name        = '{$name}',
                location    = '{$location}',
                total_seats = {$total_seats},
                is_active   = {$is_active}
            where id = {$id}";
    return sql_query($sql);
}

/* ---------------------------------------
   7) 삭제 (★ soft delete 방식)
--------------------------------------- */
function delete_lounge($id) {
    $id = (int)$id;

    $sql = "update cn_lounge
            set is_active = 0
            where id = {$id}";
    return sql_query($sql);
}

?>
