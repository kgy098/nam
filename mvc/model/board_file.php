<?php
/* cn_board_file.php
 * g5_board_file 전용 CRUD
 */

/** 
 * 단일 첨부파일 조회 (bo_table + wr_id + bf_no)
 */
function get_board_file($bo_table, $wr_id, $bf_no = 0)
{
    $bo_table = sql_escape_string($bo_table);
    $wr_id    = intval($wr_id);
    $bf_no    = intval($bf_no);

    $sql = "SELECT *
            FROM g5_board_file
            WHERE bo_table = '{$bo_table}'
              AND wr_id = {$wr_id}
              AND bf_no = {$bf_no}
            LIMIT 1";

    return sql_fetch($sql);
}


/** 
 * wr_id 의 모든 파일 리스트 조회 (여러 파일 지원 가능)
 */
function get_board_file_list($bo_table, $wr_id)
{
    $bo_table = sql_escape_string($bo_table);
    $wr_id    = intval($wr_id);

    $sql = "SELECT *
            FROM g5_board_file
            WHERE bo_table = '{$bo_table}'
              AND wr_id = {$wr_id}
            ORDER BY bf_no ASC";

    $result = sql_query($sql);
    $list = [];

    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }

    return $list;
}


/**
 * 첨부파일 저장
 * $data: [
 *   'bo_table', 'wr_id', 'bf_no',
 *   'bf_source', 'bf_file', 'bf_filesize',
 *   'bf_width', 'bf_height', 'bf_type'
 * ]
 */
function insert_board_file($data)
{
    $bo_table   = sql_escape_string($data['bo_table']);
    $wr_id      = intval($data['wr_id']);
    $bf_no      = intval($data['bf_no']);
    $bf_source  = sql_escape_string($data['bf_source']);
    $bf_file    = sql_escape_string($data['bf_file']);
    $bf_filesize= intval($data['bf_filesize']);
    $bf_width   = intval($data['bf_width']);
    $bf_height  = intval($data['bf_height']);
    $bf_type    = intval($data['bf_type']);

    $sql = "INSERT INTO g5_board_file
            SET bo_table   = '{$bo_table}',
                wr_id      = {$wr_id},
                bf_no      = {$bf_no},
                bf_source  = '{$bf_source}',
                bf_file    = '{$bf_file}',
                bf_filesize= {$bf_filesize},
                bf_width   = {$bf_width},
                bf_height  = {$bf_height},
                bf_type    = {$bf_type},
                bf_datetime = NOW()";

    return sql_query($sql);
}


/**
 * 특정 파일 삭제 (DB only)  
 * 실제 unlink는 컨트롤러에서 수행하도록 분리
 */
function delete_board_file($bo_table, $wr_id, $bf_no = 0)
{
    $bo_table = sql_escape_string($bo_table);
    $wr_id    = intval($wr_id);
    $bf_no    = intval($bf_no);

    $sql = "DELETE FROM g5_board_file
            WHERE bo_table = '{$bo_table}'
              AND wr_id = {$wr_id}
              AND bf_no = {$bf_no}";

    return sql_query($sql);
}


/**
 * wr_id 전체 파일 삭제 (DB only)
 */
function delete_board_file_all($bo_table, $wr_id)
{
    $bo_table = sql_escape_string($bo_table);
    $wr_id    = intval($wr_id);

    $sql = "DELETE FROM g5_board_file
            WHERE bo_table = '{$bo_table}'
              AND wr_id = {$wr_id}";

    return sql_query($sql);
}
?>
