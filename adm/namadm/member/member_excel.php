<?php
// member_excel.php

// DB, 공통 함수 사용 가능해야 하므로 _common.php 포함
include_once('../../../common.php');  // 경로는 너 프로젝트 구조에 맞게 수정

$table = isset($g5['member_table']) ? $g5['member_table'] : 'g5_member';

function esc($s){
    if(function_exists('sql_escape_string')) return sql_escape_string($s);
    return addslashes($s);
}

// ===== 검색 조건 동일하게 구성 =====
$field   = trim($_REQUEST['field'] ?? '');
$keyword = trim($_REQUEST['keyword'] ?? '');
$role    = trim($_REQUEST['role'] ?? '');
$class   = trim($_REQUEST['class'] ?? '');
$left_yn = trim($_REQUEST['left_yn'] ?? '');

$dt_from='';
$dt_to='';

// start_date/dt_from 통합 처리
if(!empty($_REQUEST['dt_from']))      $dt_from = trim($_REQUEST['dt_from']);
else if(!empty($_REQUEST['start_date'])) $dt_from = trim($_REQUEST['start_date']);

if(!empty($_REQUEST['dt_to']))        $dt_to = trim($_REQUEST['dt_to']);
else if(!empty($_REQUEST['end_date']))   $dt_to = trim($_REQUEST['end_date']);

// 날짜 형식 검증
if($dt_from!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_from)) $dt_from='';
if($dt_to!==''   && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_to))   $dt_to='';

if($dt_from!=='' && $dt_to!=='' && $dt_from > $dt_to){
    $tmp = $dt_from;
    $dt_from = $dt_to;
    $dt_to = $tmp;
}

$where = "1";

if($field!=='' && $keyword!==''){
    $f = preg_replace('/[^a-z0-9_]/i','',$field);
    $k = esc($keyword);
    $where .= " AND {$f} LIKE '%{$k}%'";
}
if($role!=='')  $where .= " AND role='".esc($role)."'";
if($class!=='') $where .= " AND class=".(int)$class;
if($left_yn==='Y') $where .= " AND mb_leave_date<>''";
if($left_yn==='N') $where .= " AND mb_leave_date=''";

if($dt_from!=='' && $dt_to!==''){
    $where .= " AND mb_datetime>='".esc($dt_from)." 00:00:00' AND mb_datetime<='".esc($dt_to)." 23:59:59'";
} else if($dt_from!==''){
    $where .= " AND mb_datetime>='".esc($dt_from)." 00:00:00'";
} else if($dt_to!==''){
    $where .= " AND mb_datetime<='".esc($dt_to)." 23:59:59'";
}

// ===== 엑셀 Export 시작 =====

$filename = 'member_'.date('Ymd_His').'.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 엑셀용 BOM
echo "\xEF\xBB\xBF";

echo "<table border=\"1\">";
echo "<tr>
        <th>아이디</th>
        <th>이름</th>
        <th>역할</th>
        <th>반</th>
        <th>연락처</th>
        <th>이메일</th>
        <th>상품ID</th>
        <th>상품금액</th>
        <th>선납금</th>
        <th>잔금</th>
        <th>가입일</th>
        <th>탈퇴일</th>
      </tr>";

$sql = "
  SELECT mb_id, mb_name, role, class, mb_hp, mb_email,
         product_id, product_price, product_price_first, product_price_last,
         mb_datetime, mb_leave_date
  FROM {$table}
  WHERE {$where}
  ORDER BY mb_datetime DESC, mb_id DESC
";
$q = sql_query($sql);
error_log(__FILE__.__LINE__."\n test: " );

while($row = sql_fetch_array($q)){
    echo "<tr>";
    echo "<td>{$row['mb_id']}</td>";
    echo "<td>{$row['mb_name']}</td>";
    echo "<td>{$row['role']}</td>";
    echo "<td>{$row['class']}</td>";
    echo "<td>{$row['mb_hp']}</td>";
    echo "<td>{$row['mb_email']}</td>";
    echo "<td>{$row['product_id']}</td>";
    echo "<td>{$row['product_price']}</td>";
    echo "<td>{$row['product_price_first']}</td>";
    echo "<td>{$row['product_price_last']}</td>";
    echo "<td>{$row['mb_datetime']}</td>";
    echo "<td>{$row['mb_leave_date']}</td>";
    echo "</tr>";
}

echo "</table>";
exit;
