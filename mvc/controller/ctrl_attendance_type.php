<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = $_REQUEST['type'] ?? '';
$table = 'cn_attendance_type';
$pk    = 'id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ return function_exists('sql_escape_string') ? sql_escape_string($s) : addslashes($s); }

switch($type){

case 'ATTENDANCE_TYPE_LIST':
    $page   = max(1, (int)($_REQUEST['page'] ?? 1));
    $rows   = max(1, min(200, (int)($_REQUEST['rows'] ?? 20)));
    $offset = ($page - 1) * $rows;
    $keyword = trim($_REQUEST['keyword'] ?? '');
    $is_active = trim($_REQUEST['is_active'] ?? '');

    $where = '1';
    if($keyword !== '') { $k = esc($keyword); $where .= " AND (name LIKE '%{$k}%' OR description LIKE '%{$k}%')"; }
    if($is_active !== '') $where .= " AND is_active=".(int)$is_active;

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("SELECT {$pk}, code, name, description, is_active, created_at, updated_at
                    FROM {$table}
                    WHERE {$where}
                    ORDER BY {$pk} DESC
                    LIMIT {$offset}, {$rows}");
    while($row = sql_fetch_array($q)) $list[] = $row;

    jres(true, ['total'=>$total, 'list'=>$list, 'page'=>$page, 'rows'=>$rows]);
    break;

case 'ATTENDANCE_TYPE_GET':
    $id = (int)($_REQUEST['id'] ?? 0);
    if($id <= 0) jres(false, 'invalid id');
    $row = sql_fetch("SELECT {$pk}, code, name, description, is_active, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    if(!$row) jres(false, 'not found');
    jres(true, $row);
    break;

case 'ATTENDANCE_TYPE_CREATE':
    $code = esc(trim($_REQUEST['code'] ?? ''));
    $name = esc(trim($_REQUEST['name'] ?? ''));
    $description = esc(trim($_REQUEST['description'] ?? ''));
    $is_active = (int)($_REQUEST['is_active'] ?? 1);

    if($code === '' || $name === '') jres(false, 'required');

    $ok = sql_query("INSERT INTO {$table}
                    (code, name, description, is_active, created_at, updated_at)
                    VALUES ('{$code}', '{$name}', '{$description}', {$is_active}, NOW(), NOW())", false);
    if(!$ok) jres(false, 'insert fail');

    $new = sql_fetch("SELECT {$pk}, code, name, description, is_active, created_at, updated_at
                      FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
    jres(true, $new);
    break;

case 'ATTENDANCE_TYPE_UPDATE':
    $id = (int)($_REQUEST['id'] ?? 0);
    if($id <= 0) jres(false, 'invalid id');

    $sets = [];
    if(isset($_REQUEST['code']))        $sets[] = "code='".esc(trim($_REQUEST['code']))."'";
    if(isset($_REQUEST['name']))        $sets[] = "name='".esc(trim($_REQUEST['name']))."'";
    if(isset($_REQUEST['description'])) $sets[] = "description='".esc(trim($_REQUEST['description']))."'";
    if(isset($_REQUEST['is_active']))   $sets[] = "is_active=".(int)$_REQUEST['is_active'];
    $sets[] = "updated_at=NOW()";

    $set_sql = implode(',', $sets);
    $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false, 'update fail');

    $row = sql_fetch("SELECT {$pk}, code, name, description, is_active, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    jres(true, $row);
    break;

case 'ATTENDANCE_TYPE_DELETE':
    $id = (int)($_REQUEST['id'] ?? 0);
    if($id <= 0) jres(false, 'invalid id');
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false, 'delete fail');
    jres(true, 'deleted');
    break;

default:
    jres(false, 'invalid type');
}
