<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type=''; if(isset($_REQUEST['type'])) $type=$_REQUEST['type'];
$table='cn_video'; $pk='id';

function jres($ok,$data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data],JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ return function_exists('sql_escape_string')?sql_escape_string($s):addslashes($s); }

switch($type){

case 'VIDEO_LIST':
  $page=max(1,(int)($_REQUEST['page']??1)); $rows=max(1,min(200,(int)($_REQUEST['rows']??20))); $offset=($page-1)*$rows;
  $keyword=trim($_REQUEST['keyword']??''); $teacher=trim($_REQUEST['teacher_mb_id']??''); $visible=trim($_REQUEST['is_visible']??'');

  $where='1';
  if($keyword!==''){ $k=esc($keyword); $where.=" AND (title LIKE '%{$k}%' OR description LIKE '%{$k}%')"; }
  if($teacher!=='') $where.=" AND teacher_mb_id='".esc($teacher)."'";
  if($visible!=='') $where.=" AND is_visible=".(int)$visible;

  $total=(int)sql_fetch("SELECT COUNT(*) cnt FROM {$table} WHERE {$where}")['cnt'];

  $list=[]; $q=sql_query("SELECT {$pk}, title, url, teacher_mb_id, is_visible, description, created_at, updated_at
                          FROM {$table} WHERE {$where} ORDER BY {$pk} DESC LIMIT {$offset},{$rows}");
  for($i=0;$row=sql_fetch_array($q);$i++) $list[]=$row;

  jres(true,['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
break;

case 'VIDEO_GET':
  $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
  $row=sql_fetch("SELECT {$pk}, title, url, teacher_mb_id, is_visible, description, created_at, updated_at FROM {$table} WHERE {$pk}={$id}");
  if(!$row) jres(false,'not found'); jres(true,$row);
break;

case 'VIDEO_CREATE':
  $title=esc(trim($_REQUEST['title']??'')); $url=esc(trim($_REQUEST['url']??'')); $teacher=esc(trim($_REQUEST['teacher_mb_id']??'')); 
  $is_visible=(int)($_REQUEST['is_visible']??1); $description=esc(trim($_REQUEST['description']??''));
  if($title===''||$url==='') jres(false,'required');

  $ok=sql_query("INSERT INTO {$table}(title,url,teacher_mb_id,is_visible,description,created_at,updated_at)
                 VALUES('{$title}','{$url}','{$teacher}',{$is_visible},'{$description}',NOW(),NOW())",false);
  if(!$ok) jres(false,'insert fail');

  $new=sql_fetch("SELECT {$pk}, title, url, teacher_mb_id, is_visible, description, created_at, updated_at FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
  jres(true,$new);
break;

case 'VIDEO_UPDATE':
  $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
  $sets=[];
  if(isset($_REQUEST['title']))         $sets[]="title='".esc(trim($_REQUEST['title']))."'";
  if(isset($_REQUEST['url']))           $sets[]="url='".esc(trim($_REQUEST['url']))."'";
  if(isset($_REQUEST['teacher_mb_id'])) $sets[]="teacher_mb_id='".esc(trim($_REQUEST['teacher_mb_id']))."'";
  if(isset($_REQUEST['is_visible']))    $sets[]="is_visible=".(int)$_REQUEST['is_visible'];
  if(isset($_REQUEST['description']))   $sets[]="description='".esc(trim($_REQUEST['description']))."'";
  $sets[]="updated_at=NOW()";

  $ok=sql_query("UPDATE {$table} SET ".implode(',',$sets)." WHERE {$pk}={$id}",false);
  if(!$ok) jres(false,'update fail');

  $row=sql_fetch("SELECT {$pk}, title, url, teacher_mb_id, is_visible, description, created_at, updated_at FROM {$table} WHERE {$pk}={$id}");
  jres(true,$row);
break;

case 'VIDEO_DELETE':
  $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
  $ok=sql_query("DELETE FROM {$table} WHERE {$pk}={$id}",false);
  if(!$ok) jres(false,'delete fail'); jres(true,'deleted');
break;

default: jres(false,'invalid type');
}
