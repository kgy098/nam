<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = $_REQUEST['type'] ?? '';
$table = 'cn_attendance_type';
$pk    = 'id';

// 현재 로그인한 관리자 아이디 (없으면 빈값)
$mb_id = isset($member['mb_id']) ? $member['mb_id'] : '';


switch($type){

case 'ATTENDANCE_TYPE_LIST':
    $page   = max(1, (int)($_REQUEST['page'] ?? 1));
    $rows   = max(1, min(200, (int)($_REQUEST['rows'] ?? 20)));
    $offset = ($page - 1) * $rows;

    $keyword   = trim($_REQUEST['keyword'] ?? '');
    $is_active = trim($_REQUEST['is_active'] ?? '');

    $where = '1';
    if ($keyword !== '') {
        $k = esc($keyword);
        $where .= " AND (name LIKE '%{$k}%' OR description LIKE '%{$k}%')";
    }
    if ($is_active !== '') {
        $where .= " AND is_active=".(int)$is_active;
    }

    $cnt   = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("
        SELECT {$pk}, mb_id, name, description, is_active, sort_order, reg_dt, mod_dt
        FROM {$table}
        WHERE {$where}
        ORDER BY sort_order ASC, {$pk} ASC
        LIMIT {$offset}, {$rows}
    ");
    while ($row = sql_fetch_array($q)) $list[] = $row;

    jres(true, [
        'total' => $total,
        'list'  => $list,
        'page'  => $page,
        'rows'  => $rows
    ]);
    break;

case 'ATTENDANCE_TYPE_GET':
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = sql_fetch("
        SELECT {$pk}, mb_id, name, description, is_active, sort_order, reg_dt, mod_dt
        FROM {$table}
        WHERE {$pk} = {$id}
    ");

    if (!$row) jres(false, 'not found');
    jres(true, $row);
    break;

case 'ATTENDANCE_TYPE_CREATE':
    // mb_id 는 로그인 사용자 기준
    global $mb_id;

    $name        = esc(trim($_REQUEST['name'] ?? ''));
    $description = trim($_REQUEST['description'] ?? '');
    $is_active   = isset($_REQUEST['is_active']) ? (int)$_REQUEST['is_active'] : 1;
    $sort_order  = isset($_REQUEST['sort_order']) ? (int)$_REQUEST['sort_order'] : 0;

    if ($name === '') jres(false, 'required name');

    // description 은 null 허용
    $desc_sql = ($description === '') ? "NULL" : "'".esc($description)."'";
    $mb_id_sql = $mb_id !== '' ? "'".esc($mb_id)."'" : "NULL";

    $ok = sql_query("
        INSERT INTO {$table}
            (mb_id, name, description, is_active, sort_order)
        VALUES
            ({$mb_id_sql}, '{$name}', {$desc_sql}, {$is_active}, {$sort_order})
    ", false);

    if (!$ok) jres(false, 'insert fail');

    // 방금 등록된 row 한 건 조회
    $row = sql_fetch("
        SELECT {$pk}, mb_id, name, description, is_active, sort_order, reg_dt, mod_dt
        FROM {$table}
        ORDER BY {$pk} DESC
        LIMIT 1
    ");

    jres(true, $row);
    break;

case 'ATTENDANCE_TYPE_UPDATE':
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $sets = [];

    if (isset($_REQUEST['name'])) {
        $name = esc(trim($_REQUEST['name']));
        $sets[] = "name='{$name}'";
    }

    if (isset($_REQUEST['description'])) {
        $desc = trim($_REQUEST['description']);
        if ($desc === '') {
            $sets[] = "description=NULL";
        } else {
            $sets[] = "description='".esc($desc)."'";
        }
    }

    if (isset($_REQUEST['is_active'])) {
        $sets[] = "is_active=".(int)$_REQUEST['is_active'];
    }

    if (isset($_REQUEST['sort_order'])) {
        $sets[] = "sort_order=".(int)$_REQUEST['sort_order'];
    }

    if (empty($sets)) jres(false, 'no fields');

    // mod_dt 갱신
    $sets[] = "mod_dt=NOW()";
    $set_sql = implode(',', $sets);

    $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
    if (!$ok) jres(false, 'update fail');

    $row = sql_fetch("
        SELECT {$pk}, mb_id, name, description, is_active, sort_order, reg_dt, mod_dt
        FROM {$table}
        WHERE {$pk} = {$id}
    ");
    jres(true, $row);
    break;

case 'ATTENDANCE_TYPE_DELETE':
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    // 지금은 물리 삭제. soft delete 로 바꾸고 싶으면:
    // $ok = sql_query(\"UPDATE {$table} SET is_active=0, mod_dt=NOW() WHERE {$pk}={$id}\", false);
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);

    if (!$ok) jres(false, 'delete fail');
    jres(true, 'deleted');
    break;

default:
    jres(false, 'invalid type');
}
