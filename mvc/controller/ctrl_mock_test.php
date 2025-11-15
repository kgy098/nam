<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = 'cn_mock_test';
$pk    = 'id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ if(function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch($type){

case 'MOCK_TEST_LIST':
    $page   = isset($_REQUEST['page']) ? max(1,(int)$_REQUEST['page']) : 1;
    $rows   = isset($_REQUEST['rows']) ? max(1,min(200,(int)$_REQUEST['rows'])) : 20;
    $offset = ($page-1)*$rows;

    $mock_id    = isset($_REQUEST['mock_id']) ? (int)$_REQUEST['mock_id'] : 0;
    $subject_id = isset($_REQUEST['subject_id']) ? (int)$_REQUEST['subject_id'] : 0;
    $keyword    = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';

    $where = '1';
    if($mock_id>0)    $where .= " AND mock_id={$mock_id}";
    if($subject_id>0) $where .= " AND subject_id={$subject_id}";
    if($keyword!==''){ $k=esc($keyword); $where .= " AND (location LIKE '%{$k}%' OR description LIKE '%{$k}%')"; }

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("SELECT {$pk}, mock_id, subject_id, test_date, location, max_score, description, created_at, updated_at
                    FROM {$table}
                    WHERE {$where}
                    ORDER BY {$pk} DESC
                    LIMIT {$offset}, {$rows}");
    for($i=0; $row=sql_fetch_array($q); $i++) $list[] = $row;

    jres(true, ['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
    break;

case 'MOCK_TEST_GET':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $row = sql_fetch("SELECT {$pk}, mock_id, subject_id, test_date, location, max_score, description, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    if(!$row) jres(false,'not found');
    jres(true,$row);
    break;

case 'MOCK_TEST_CREATE':
    $mock_id    = isset($_REQUEST['mock_id']) ? (int)$_REQUEST['mock_id'] : 0;
    $subject_id = isset($_REQUEST['subject_id']) ? (int)$_REQUEST['subject_id'] : 0;
    $test_date  = isset($_REQUEST['test_date']) && $_REQUEST['test_date']!=='' ? "'".esc($_REQUEST['test_date'])."'" : 'NULL';
    $location   = isset($_REQUEST['location']) ? esc(trim($_REQUEST['location'])) : '';
    $max_score  = isset($_REQUEST['max_score']) ? (int)$_REQUEST['max_score'] : 0;
    $description= isset($_REQUEST['description']) ? esc(trim($_REQUEST['description'])) : '';

    if($mock_id<=0 || $subject_id<=0) jres(false,'required');

    $ok = sql_query("INSERT INTO {$table}
                    (mock_id, subject_id, test_date, location, max_score, description, created_at, updated_at)
                    VALUES ({$mock_id}, {$subject_id}, {$test_date}, '{$location}', {$max_score}, '{$description}', NOW(), NOW())", false);
    if(!$ok) jres(false,'insert fail');

    $new = sql_fetch("SELECT {$pk}, mock_id, subject_id, test_date, location, max_score, description, created_at, updated_at
                      FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
    jres(true,$new);
    break;

case 'MOCK_TEST_UPDATE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');

    $sets = [];
    if(isset($_REQUEST['mock_id']))    $sets[] = "mock_id=".(int)$_REQUEST['mock_id'];
    if(isset($_REQUEST['subject_id'])) $sets[] = "subject_id=".(int)$_REQUEST['subject_id'];
    if(array_key_exists('test_date',$_REQUEST)) $sets[] = "test_date=".($_REQUEST['test_date']===''?"NULL":"'".esc($_REQUEST['test_date'])."'");
    if(isset($_REQUEST['location']))   $sets[] = "location='".esc(trim($_REQUEST['location']))."'";
    if(isset($_REQUEST['max_score']))  $sets[] = "max_score=".(int)$_REQUEST['max_score'];
    if(isset($_REQUEST['description']))$sets[] = "description='".esc(trim($_REQUEST['description']))."'";
    $sets[] = "updated_at=NOW()";

    $set_sql = implode(',', $sets);
    $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'update fail');

    $row = sql_fetch("SELECT {$pk}, mock_id, subject_id, test_date, location, max_score, description, created_at, updated_at
                      FROM {$table} WHERE {$pk}={$id}");
    jres(true,$row);
    break;

case 'MOCK_TEST_DELETE':
    $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
    if($id<=0) jres(false,'invalid id');
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);
    if(!$ok) jres(false,'delete fail');
    jres(true,'deleted');
    break;

default:
    jres(false,'invalid type');
}
