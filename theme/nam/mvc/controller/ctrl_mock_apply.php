<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = 'cn_mock_apply';
$pk    = 'id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ if(function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch($type){

case 'MOCK_APPLY_LIST':
    $page   = isset($_REQUEST['page']) ? max(1,(int)$_REQUEST['page']) : 1;
    $rows   = isset($_REQUEST['rows']) ? max(1,min(200,(int)$_REQUEST['rows'])) : 20;
    $offset = ($page-1)*$rows;

    $mb_id   = isset($_REQUEST['mb_id']) ? trim($_REQUEST['mb_id']) : '';
    $mock_id = isset($_REQUEST['mock_id']) ? (int)$_REQUEST['mock_id'] : 0;
    $status  = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';

    $where = '1';
    if($mb_id!=='')   $where .= " AND mb_id='".esc($mb_id)."'";
    if($mock_id>0)    $where .= " AND mock_id={$mock_id}";
    if($status!=='')  $where .= " AND status='".esc($status)."'";

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("SELECT {$pk}, mb_id, mock_id, applied_at, status, score, feedback, created_at, updated_at
                    FROM {$table}
                    WHERE {$where}
                    ORDER BY {$pk} DESC
                    LIMIT {$offset}, {$rows}");
    for($i=0; $row=sql_fetch_array($q); $i++) $list[] = $row;

    jres(true, ['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
    break;

case 'MOCK_APPLY_GET':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $row = sql_fetch("SELECT {$pk}, mb_id, mock_id, applied_at, status, score, feedback, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    if(!$row) jres(false,'not found');
    jres(true,$row);
    break;

case 'MOCK_APPLY_CREATE':
    $mb_id   = isset($_REQUEST['mb_id']) ? esc(trim($_REQUEST['mb_id'])) : '';
    $mock_id = isset($_REQUEST['mock_id']) ? (int)$_REQUEST['mock_id'] : 0;
    $status  = isset($_REQUEST['status']) ? esc(trim($_REQUEST['status'])) : 'APPLIED';
    $score   = isset($_REQUEST['score']) ? (int)$_REQUEST['score'] : 0;
    $feedback= isset($_REQUEST['feedback']) ? esc(trim($_REQUEST['feedback'])) : '';

    if($mb_id==='' || $mock_id<=0) jres(false,'required');

    $ok = sql_query("INSERT INTO {$table}
                    (mb_id, mock_id, applied_at, status, score, feedback, created_at, updated_at)
                    VALUES ('{$mb_id}', {$mock_id}, NOW(), '{$status}', {$score}, '{$feedback}', NOW(), NOW())", false);
    if(!$ok) jres(false,'insert fail');

    $new = sql_fetch("SELECT {$pk}, mb_id, mock_id, applied_at, status, score, feedback, created_at, updated_at
                      FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
    jres(true,$new);
    break;

case 'MOCK_APPLY_UPDATE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');

    $sets = [];
    if(isset($_REQUEST['mb_id']))    $sets[] = "mb_id='".esc(trim($_REQUEST['mb_id']))."'";
    if(isset($_REQUEST['mock_id']))  $sets[] = "mock_id=".(int)$_REQUEST['mock_id'];
    if(isset($_REQUEST['status']))   $sets[] = "status='".esc(trim($_REQUEST['status']))."'";
    if(isset($_REQUEST['score']))    $sets[] = "score=".(int)$_REQUEST['score'];
    if(isset($_REQUEST['feedback'])) $sets[] = "feedback='".esc(trim($_REQUEST['feedback']))."'";
    $sets[] = "updated_at=NOW()";

    $set_sql = implode(',', $sets);
    $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'update fail');

    $row = sql_fetch("SELECT {$pk}, mb_id, mock_id, applied_at, status, score, feedback, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    jres(true,$row);
    break;

case 'MOCK_APPLY_DELETE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'delete fail');
    jres(true,'deleted');
    break;

default:
    jres(false,'invalid type');
}
