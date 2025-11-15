<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = 'cn_notify_log';
$pk    = 'id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ if(function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch($type){

case 'NOTIFY_LOG_LIST':
    $page   = isset($_REQUEST['page']) ? max(1,(int)$_REQUEST['page']) : 1;
    $rows   = isset($_REQUEST['rows']) ? max(1,min(200,(int)$_REQUEST['rows'])) : 20;
    $offset = ($page-1)*$rows;

    $mb_id  = isset($_REQUEST['mb_id']) ? trim($_REQUEST['mb_id']) : '';
    $type_f = isset($_REQUEST['ntype']) ? trim($_REQUEST['ntype']) : ''; // type 필터
    $is_read= isset($_REQUEST['is_read']) ? trim($_REQUEST['is_read']) : '';

    $where = '1';
    if($mb_id!=='')   $where .= " AND mb_id='".esc($mb_id)."'";
    if($type_f!=='')  $where .= " AND type='".esc($type_f)."'";
    if($is_read!=='') $where .= " AND is_read=".(int)$is_read;

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("SELECT {$pk}, mb_id, type, title, message, is_read, sent_at, created_at, updated_at
                    FROM {$table}
                    WHERE {$where}
                    ORDER BY {$pk} DESC
                    LIMIT {$offset}, {$rows}");
    for($i=0; $row=sql_fetch_array($q); $i++) $list[] = $row;

    jres(true, ['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
    break;

case 'NOTIFY_LOG_GET':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $row = sql_fetch("SELECT {$pk}, mb_id, type, title, message, is_read, sent_at, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    if(!$row) jres(false,'not found');
    jres(true,$row);
    break;

case 'NOTIFY_LOG_CREATE':
    $mb_id   = isset($_REQUEST['mb_id']) ? esc(trim($_REQUEST['mb_id'])) : '';
    $type_v  = isset($_REQUEST['type']) ? esc(trim($_REQUEST['type'])) : 'SYSTEM';
    $title   = isset($_REQUEST['title']) ? esc(trim($_REQUEST['title'])) : '';
    $message = isset($_REQUEST['message']) ? esc(trim($_REQUEST['message'])) : '';
    $is_read = isset($_REQUEST['is_read']) ? (int)$_REQUEST['is_read'] : 0;
    $sent_at = (isset($_REQUEST['sent_at']) && $_REQUEST['sent_at']!=='') ? "'".esc($_REQUEST['sent_at'])."'" : 'NOW()';

    if($mb_id==='' || $title==='') jres(false,'required');

    $ok = sql_query("INSERT INTO {$table}
                    (mb_id, type, title, message, is_read, sent_at, created_at, updated_at)
                    VALUES ('{$mb_id}', '{$type_v}', '{$title}', '{$message}', {$is_read}, {$sent_at}, NOW(), NOW())", false);
    if(!$ok) jres(false,'insert fail');

    $new = sql_fetch("SELECT {$pk}, mb_id, type, title, message, is_read, sent_at, created_at, updated_at
                      FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
    jres(true,$new);
    break;

case 'NOTIFY_LOG_UPDATE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');

    $sets = [];
    if(isset($_REQUEST['mb_id']))   $sets[] = "mb_id='".esc(trim($_REQUEST['mb_id']))."'";
    if(isset($_REQUEST['type']))    $sets[] = "type='".esc(trim($_REQUEST['type']))."'";
    if(isset($_REQUEST['title']))   $sets[] = "title='".esc(trim($_REQUEST['title']))."'";
    if(isset($_REQUEST['message'])) $sets[] = "message='".esc(trim($_REQUEST['message']))."'";
    if(isset($_REQUEST['is_read'])) $sets[] = "is_read=".(int)$_REQUEST['is_read'];
    if(array_key_exists('sent_at',$_REQUEST)) $sets[] = "sent_at=".($_REQUEST['sent_at']===''?"NULL":"'".esc($_REQUEST['sent_at'])."'");
    $sets[] = "updated_at=NOW()";

    $set_sql = implode(',', $sets);
    $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'update fail');

    $row = sql_fetch("SELECT {$pk}, mb_id, type, title, message, is_read, sent_at, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    jres(true,$row);
    break;

case 'NOTIFY_LOG_DELETE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'delete fail');
    jres(true,'deleted');
    break;

default:
    jres(false,'invalid type');
}
