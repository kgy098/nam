<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = 'cn_qna';
$pk    = 'id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ if(function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch($type){

case 'QNA_LIST':
    $page=max(1,(int)($_REQUEST['page']??1)); $rows=max(1,min(200,(int)($_REQUEST['rows']??20))); $offset=($page-1)*$rows;
    $mb_id=trim($_REQUEST['mb_id']??''); $status=trim($_REQUEST['status']??''); $keyword=trim($_REQUEST['keyword']??'');

    $where='1';
    if($mb_id!=='')   $where.=" AND mb_id='".esc($mb_id)."'";
    if($status!=='')  $where.=" AND status='".esc($status)."'";
    if($keyword!==''){ $k=esc($keyword); $where.=" AND (title LIKE '%{$k}%' OR content LIKE '%{$k}%')"; }

    $total=(int)sql_fetch("SELECT COUNT(*) cnt FROM {$table} WHERE {$where}")['cnt'];

    $list=[]; $q=sql_query("SELECT {$pk}, mb_id, title, content, status, answer, created_at, updated_at
                            FROM {$table} WHERE {$where} ORDER BY {$pk} DESC LIMIT {$offset}, {$rows}");
    for($i=0;$row=sql_fetch_array($q);$i++) $list[]=$row;

    jres(true,['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
break;

case 'QNA_GET':
    $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
    $row=sql_fetch("SELECT {$pk}, mb_id, title, content, status, answer, created_at, updated_at FROM {$table} WHERE {$pk}={$id}");
    if(!$row) jres(false,'not found'); jres(true,$row);
break;

case 'QNA_CREATE':
    $mb_id=esc(trim($_REQUEST['mb_id']??'')); $title=esc(trim($_REQUEST['title']??'')); $content=esc(trim($_REQUEST['content']??'')); 
    $status=esc(trim($_REQUEST['status']??'OPEN')); $answer=esc(trim($_REQUEST['answer']??''));
    if($mb_id===''||$title==='') jres(false,'required');

    $ok=sql_query("INSERT INTO {$table}(mb_id,title,content,status,answer,created_at,updated_at)
                   VALUES('{$mb_id}','{$title}','{$content}','{$status}','{$answer}',NOW(),NOW())",false);
    if(!$ok) jres(false,'insert fail');

    $new=sql_fetch("SELECT {$pk}, mb_id, title, content, status, answer, created_at, updated_at FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
    jres(true,$new);
break;

case 'QNA_UPDATE':
    $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
    $sets=[];
    if(isset($_REQUEST['mb_id']))   $sets[]="mb_id='".esc(trim($_REQUEST['mb_id']))."'";
    if(isset($_REQUEST['title']))   $sets[]="title='".esc(trim($_REQUEST['title']))."'";
    if(isset($_REQUEST['content'])) $sets[]="content='".esc(trim($_REQUEST['content']))."'";
    if(isset($_REQUEST['status']))  $sets[]="status='".esc(trim($_REQUEST['status']))."'";
    if(isset($_REQUEST['answer']))  $sets[]="answer='".esc(trim($_REQUEST['answer']))."'";
    $sets[]="updated_at=NOW()";
    $ok=sql_query("UPDATE {$table} SET ".implode(',',$sets)." WHERE {$pk}={$id}",false);
    if(!$ok) jres(false,'update fail');

    $row=sql_fetch("SELECT {$pk}, mb_id, title, content, status, answer, created_at, updated_at FROM {$table} WHERE {$pk}={$id}");
    jres(true,$row);
break;

case 'QNA_DELETE':
    $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
    $ok=sql_query("DELETE FROM {$table} WHERE {$pk}={$id}",false);
    if(!$ok) jres(false,'delete fail'); jres(true,'deleted');
break;

default: jres(false,'invalid type');
}
