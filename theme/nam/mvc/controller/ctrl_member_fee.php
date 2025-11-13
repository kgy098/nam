<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = 'cn_member_fee';
$pk    = 'id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ if(function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch($type){

case 'MEMBER_FEE_LIST':
    $page   = isset($_REQUEST['page']) ? max(1,(int)$_REQUEST['page']) : 1;
    $rows   = isset($_REQUEST['rows']) ? max(1,min(200,(int)$_REQUEST['rows'])) : 20;
    $offset = ($page-1)*$rows;

    $mb_id      = isset($_REQUEST['mb_id']) ? trim($_REQUEST['mb_id']) : '';
    $status     = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
    $month_from = isset($_REQUEST['month_from']) ? trim($_REQUEST['month_from']) : ''; // YYYY-MM
    $month_to   = isset($_REQUEST['month_to']) ? trim($_REQUEST['month_to']) : '';     // YYYY-MM
    $keyword    = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

    $where = '1';
    if($mb_id!=='')      $where .= " AND mb_id='".esc($mb_id)."'";
    if($status!=='')     $where .= " AND status='".esc($status)."'";
    if($month_from!=='') $where .= " AND fee_month>='".esc($month_from)."'";
    if($month_to!=='')   $where .= " AND fee_month<='".esc($month_to)."'";
    if($keyword!==''){ $k=esc($keyword); $where .= " AND (memo LIKE '%{$k}%')"; }

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("SELECT {$pk}, mb_id, fee_month, amount, status, due_date, paid_at, method, memo, created_at, updated_at
                    FROM {$table}
                    WHERE {$where}
                    ORDER BY {$pk} DESC
                    LIMIT {$offset}, {$rows}");
    for($i=0; $row=sql_fetch_array($q); $i++) $list[] = $row;

    jres(true, ['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
    break;

case 'MEMBER_FEE_GET':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $row = sql_fetch("SELECT {$pk}, mb_id, fee_month, amount, status, due_date, paid_at, method, memo, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    if(!$row) jres(false,'not found');
    jres(true,$row);
    break;

case 'MEMBER_FEE_CREATE':
    $mb_id     = isset($_REQUEST['mb_id']) ? esc(trim($_REQUEST['mb_id'])) : '';
    $fee_month = isset($_REQUEST['fee_month']) ? esc(trim($_REQUEST['fee_month'])) : ''; // YYYY-MM
    $amount    = isset($_REQUEST['amount']) ? (int)$_REQUEST['amount'] : 0;
    $status    = isset($_REQUEST['status']) ? esc(trim($_REQUEST['status'])) : 'UNPAID';
    $due_date  = (isset($_REQUEST['due_date']) && $_REQUEST['due_date']!=='') ? "'".esc($_REQUEST['due_date'])."'" : 'NULL';
    $paid_at   = (isset($_REQUEST['paid_at'])  && $_REQUEST['paid_at']!=='')  ? "'".esc($_REQUEST['paid_at'])."'"  : 'NULL';
    $method    = isset($_REQUEST['method']) ? esc(trim($_REQUEST['method'])) : '';
    $memo      = isset($_REQUEST['memo']) ? esc(trim($_REQUEST['memo'])) : '';

    if($mb_id==='' || $fee_month==='' || $amount<=0) jres(false,'required');

    $ok = sql_query("INSERT INTO {$table}
                    (mb_id, fee_month, amount, status, due_date, paid_at, method, memo, created_at, updated_at)
                    VALUES ('{$mb_id}','{$fee_month}',{$amount},'{$status}',{$due_date},{$paid_at},'{$method}','{$memo}',NOW(),NOW())", false);
    if(!$ok) jres(false,'insert fail');

    $new = sql_fetch("SELECT {$pk}, mb_id, fee_month, amount, status, due_date, paid_at, method, memo, created_at, updated_at
                      FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
    jres(true,$new);
    break;

case 'MEMBER_FEE_UPDATE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');

    $sets = [];
    if(isset($_REQUEST['mb_id']))      $sets[] = "mb_id='".esc(trim($_REQUEST['mb_id']))."'";
    if(isset($_REQUEST['fee_month']))  $sets[] = "fee_month='".esc(trim($_REQUEST['fee_month']))."'";
    if(isset($_REQUEST['amount']))     $sets[] = "amount=".(int)$_REQUEST['amount'];
    if(isset($_REQUEST['status']))     $sets[] = "status='".esc(trim($_REQUEST['status']))."'";
    if(array_key_exists('due_date', $_REQUEST)) $sets[] = "due_date=".($_REQUEST['due_date']===''?"NULL":"'".esc($_REQUEST['due_date'])."'");
    if(array_key_exists('paid_at', $_REQUEST))  $sets[] = "paid_at=".($_REQUEST['paid_at']===''?"NULL":"'".esc($_REQUEST['paid_at'])."'");
    if(isset($_REQUEST['method']))     $sets[] = "method='".esc(trim($_REQUEST['method']))."'";
    if(isset($_REQUEST['memo']))       $sets[] = "memo='".esc(trim($_REQUEST['memo']))."'";
    $sets[] = "updated_at=NOW()";

    $set_sql = implode(',', $sets);
    $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'update fail');

    $row = sql_fetch("SELECT {$pk}, mb_id, fee_month, amount, status, due_date, paid_at, method, memo, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    jres(true,$row);
    break;

case 'MEMBER_FEE_DELETE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'delete fail');
    jres(true,'deleted');
    break;

default:
    jres(false,'invalid type');
}
