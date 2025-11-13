<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type=$_REQUEST['type']??''; $table='cn_study_report'; $pk='id';

function jres($ok,$data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data],JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ return function_exists('sql_escape_string')?sql_escape_string($s):addslashes($s); }

switch($type){

case 'STUDY_REPORT_LIST':
  $page=max(1,(int)($_REQUEST['page']??1)); $rows=max(1,min(200,(int)($_REQUEST['rows']??20))); $offset=($page-1)*$rows;
  $mb_id=trim($_REQUEST['mb_id']??''); $date_from=trim($_REQUEST['date_from']??''); $date_to=trim($_REQUEST['date_to']??''); $keyword=trim($_REQUEST['keyword']??'');

  $where='1';
  if($mb_id!=='')      $where.=" AND mb_id='".esc($mb_id)."'";
  if($date_from!=='')  $where.=" AND report_date>='".esc($date_from)."'";
  if($date_to!=='')    $where.=" AND report_date<='".esc($date_to)."'";
  if($keyword!==''){ $k=esc($keyword); $where.=" AND (summary LIKE '%{$k}%' OR detail LIKE '%{$k}%')"; }

  $total=(int)sql_fetch("SELECT COUNT(*) cnt FROM {$table} WHERE {$where}")['cnt'];

  $list=[]; $q=sql_query("SELECT {$pk}, mb_id, report_date, study_minutes, summary, detail, created_at, updated_at
                          FROM {$table} WHERE {$where} ORDER BY report_date DESC, {$pk} DESC LIMIT {$offset},{$rows}");
  for($i=0;$row=sql_fetch_array($q);$i++) $list[]=$row;

  jres(true,['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
break;

case 'STUDY_REPORT_GET':
  $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
  $row=sql_fetch("SELECT {$pk}, mb_id, report_date, study_minutes, summary, detail, created_at, updated_at FROM {$table} WHERE {$pk}={$id}");
  if(!$row) jres(false,'not found'); jres(true,$row);
break;

case 'STUDY_REPORT_CREATE':
  $mb_id=esc(trim($_REQUEST['mb_id']??'')); $report_date=esc(trim($_REQUEST['report_date']??'')); 
  $study_minutes=(int)($_REQUEST['study_minutes']??0); $summary=esc(trim($_REQUEST['summary']??'')); $detail=esc(trim($_REQUEST['detail']??''));
  if($mb_id===''||$report_date==='') jres(false,'required');

  $ok=sql_query("INSERT INTO {$table}(mb_id,report_date,study_minutes,summary,detail,created_at,updated_at)
                 VALUES('{$mb_id}','{$report_date}',{$study_minutes},'{$summary}','{$detail}',NOW(),NOW())",false);
  if(!$ok) jres(false,'insert fail');

  $new=sql_fetch("SELECT {$pk}, mb_id, report_date, study_minutes, summary, detail, created_at, updated_at FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
  jres(true,$new);
break;

case 'STUDY_REPORT_UPDATE':
  $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
  $sets=[];
  if(isset($_REQUEST['mb_id']))         $sets[]="mb_id='".esc(trim($_REQUEST['mb_id']))."'";
  if(isset($_REQUEST['report_date']))   $sets[]="report_date='".esc(trim($_REQUEST['report_date']))."'";
  if(isset($_REQUEST['study_minutes'])) $sets[]="study_minutes=".(int)$_REQUEST['study_minutes'];
  if(isset($_REQUEST['summary']))       $sets[]="summary='".esc(trim($_REQUEST['summary']))."'";
  if(isset($_REQUEST['detail']))        $sets[]="detail='".esc(trim($_REQUEST['detail']))."'";
  $sets[]="updated_at=NOW()";

  $ok=sql_query("UPDATE {$table} SET ".implode(',',$sets)." WHERE {$pk}={$id}",false);
  if(!$ok) jres(false,'update fail');

  $row=sql_fetch("SELECT {$pk}, mb_id, report_date, study_minutes, summary, detail, created_at, updated_at FROM {$table} WHERE {$pk}={$id}");
  jres(true,$row);
break;

case 'STUDY_REPORT_DELETE':
  $id=(int)($_REQUEST['id']??0); if($id<=0) jres(false,'invalid id');
  $ok=sql_query("DELETE FROM {$table} WHERE {$pk}={$id}",false);
  if(!$ok) jres(false,'delete fail'); jres(true,'deleted');
break;

default: jres(false,'invalid type');
}
