<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type  = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = isset($g5['member_table']) ? $g5['member_table'] : 'g5_member';
$pk    = 'mb_id';

function jres($ok, $data=null){ echo json_encode(['result'=>$ok?'SUCCESS':'FAIL','data'=>$data], JSON_UNESCAPED_UNICODE); exit; }
function esc($s){ if(function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch($type){

case 'MEMBER_LIST':
    $page   = isset($_REQUEST['page']) ? max(1,(int)$_REQUEST['page']) : 1;
    $rows   = isset($_REQUEST['rows']) ? max(1,min(200,(int)$_REQUEST['rows'])) : 20;
    $offset = ($page-1)*$rows;

    $field   = isset($_REQUEST['field']) ? trim($_REQUEST['field']) : ''; // mb_id|mb_name|mb_hp|mb_email
    $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
    $role    = isset($_REQUEST['role']) ? trim($_REQUEST['role']) : '';   // STUDENT|TEACHER|ADMIN
    $class   = isset($_REQUEST['class']) ? trim($_REQUEST['class']) : ''; // 반 번호
    $left_yn = isset($_REQUEST['left_yn']) ? trim($_REQUEST['left_yn']) : ''; // 탈퇴여부(Y/N)

    // ✅ view단: start_date, end_date 로 들어오는 걸 우선 사용
    //    (dt_from, dt_to도 같이 보내면 그대로 사용 가능)
    $dt_from = '';
    $dt_to   = '';

    if(isset($_REQUEST['dt_from']) && $_REQUEST['dt_from']!==''){
        $dt_from = trim($_REQUEST['dt_from']);
    } elseif(isset($_REQUEST['start_date']) && $_REQUEST['start_date']!==''){
        $dt_from = trim($_REQUEST['start_date']);
    }

    if(isset($_REQUEST['dt_to']) && $_REQUEST['dt_to']!==''){
        $dt_to = trim($_REQUEST['dt_to']);
    } elseif(isset($_REQUEST['end_date']) && $_REQUEST['end_date']!==''){
        $dt_to = trim($_REQUEST['end_date']);
    }

    // 날짜 형식 검증 (YYYY-MM-DD만 허용)
    if($dt_from!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_from)) $dt_from = '';
    if($dt_to!=='' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_to))     $dt_to   = '';

    // from/to 둘 다 있는 경우, 순서가 뒤집혀 있으면 교환
    if($dt_from!=='' && $dt_to!==''){
        if($dt_from > $dt_to){
            $tmp     = $dt_from;
            $dt_from = $dt_to;
            $dt_to   = $tmp;
        }
    }

    $where = '1';
    if($field!=='' && $keyword!==''){
        $k = esc($keyword);
        $f = preg_replace('/[^a-z0-9_]/i','',$field);
        $where .= " AND {$f} LIKE '%{$k}%'";
    }
    if($role!=='')  $where .= " AND role='".esc($role)."'";
    if($class!=='') $where .= " AND class=".(int)$class;
    if($left_yn==='Y') $where .= " AND mb_leave_date<>''";
    if($left_yn==='N') $where .= " AND mb_leave_date=''";

    // 가입일 구간 조건
    if($dt_from!=='' && $dt_to!==''){
        $where .= " AND mb_datetime>='".esc($dt_from)." 00:00:00' AND mb_datetime<='".esc($dt_to)." 23:59:59'";
    } else if($dt_from!==''){
        $where .= " AND mb_datetime>='".esc($dt_from)." 00:00:00'";
    } else if($dt_to!==''){
        $where .= " AND mb_datetime<='".esc($dt_to)." 23:59:59'";
    }

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $list = [];
    $q = sql_query("
      SELECT mb_id, mb_name, role, class, mb_hp, mb_email, mb_sex,
             product_id, product_price, product_price_first, product_price_last,
             mb_datetime, mb_leave_date, mb_today_login, mb_login_ip, mb_level
      FROM {$table}
      WHERE {$where}
      ORDER BY mb_datetime DESC, mb_id DESC
      LIMIT {$offset}, {$rows}
    ");
    for($i=0; $row=sql_fetch_array($q); $i++) $list[] = $row;

    jres(true, ['total'=>$total,'list'=>$list,'page'=>$page,'rows'=>$rows]);
break;


case 'MEMBER_GET':
    $id = isset($_REQUEST['mb_id']) ? trim($_REQUEST['mb_id']) : '';
    if($id==='') jres(false,'invalid mb_id');
    $row = sql_fetch("
      SELECT mb_id, mb_name, auth_no, role, class, mb_hp, mb_email, mb_sex,
             mb_zip1, mb_zip2, mb_addr1, mb_addr2, mb_addr3, mb_addr_jibeon,
             product_id, product_price, product_price_first, product_price_last,
             mb_datetime, mb_leave_date, mb_today_login, mb_login_ip, mb_level
      FROM {$table}
      WHERE {$pk}='".esc($id)."'
      LIMIT 1
    ");
    if(!$row) jres(false,'not found');
    jres(true,$row);
break;

case 'MEMBER_CREATE':
    $mb_id   = isset($_REQUEST['mb_id']) ? esc(trim($_REQUEST['mb_id'])) : '';
    $mb_name = isset($_REQUEST['mb_name']) ? esc(trim($_REQUEST['mb_name'])) : '';
    if($mb_id==='' || $mb_name==='') jres(false,'required');

    $role  = isset($_REQUEST['role']) ? esc(trim($_REQUEST['role'])) : 'STUDENT';
    $class = isset($_REQUEST['class']) ? (int)$_REQUEST['class'] : 'NULL';
    $mb_hp = isset($_REQUEST['mb_hp']) ? esc(trim($_REQUEST['mb_hp'])) : '';
    $mb_email = isset($_REQUEST['mb_email']) ? esc(trim($_REQUEST['mb_email'])) : '';
    $mb_sex = isset($_REQUEST['mb_sex']) ? esc(trim($_REQUEST['mb_sex'])) : '';
    $mb_zip1 = esc(trim($_REQUEST['mb_zip1'] ?? ''));
    $mb_zip2 = esc(trim($_REQUEST['mb_zip2'] ?? ''));
    $mb_addr1 = esc(trim($_REQUEST['mb_addr1'] ?? ''));
    $mb_addr2 = esc(trim($_REQUEST['mb_addr2'] ?? ''));
    $mb_addr3 = esc(trim($_REQUEST['mb_addr3'] ?? ''));
    $mb_addr_jibeon = esc(trim($_REQUEST['mb_addr_jibeon'] ?? ''));

    $product_id = isset($_REQUEST['product_id']) ? (strlen($_REQUEST['product_id'])? (int)$_REQUEST['product_id'] : 'NULL') : 'NULL';
    $product_price = isset($_REQUEST['product_price']) ? (int)$_REQUEST['product_price'] : 0;
    $product_price_first = isset($_REQUEST['product_price_first']) ? (int)$_REQUEST['product_price_first'] : 0;
    $product_price_last  = isset($_REQUEST['product_price_last'])  ? (int)$_REQUEST['product_price_last']  : 0;

    $sql = "
      INSERT INTO {$table}
      (mb_id, mb_name, role, class, mb_hp, mb_email, mb_sex,
       mb_zip1, mb_zip2, mb_addr1, mb_addr2, mb_addr3, mb_addr_jibeon,
       product_id, product_price, product_price_first, product_price_last,
       mb_datetime)
      VALUES
      ('{$mb_id}','{$mb_name}','{$role}',".($class==='NULL'?'NULL':$class).",
       '{$mb_hp}','{$mb_email}','{$mb_sex}',
       '{$mb_zip1}','{$mb_zip2}','{$mb_addr1}','{$mb_addr2}','{$mb_addr3}','{$mb_addr_jibeon}',
       ".($product_id==='NULL'?'NULL':$product_id).",
       {$product_price}, {$product_price_first}, {$product_price_last},
       NOW()
      )
    ";
    $ok = sql_query($sql, false);
    if(!$ok) jres(false,'insert fail');

    $row = sql_fetch("SELECT mb_id, mb_name, role, class, mb_hp, mb_email, mb_sex,
                             product_id, product_price, product_price_first, product_price_last,
                             mb_datetime
                      FROM {$table} WHERE {$pk}='{$mb_id}'");
    jres(true,$row);
break;

case 'MEMBER_UPDATE':
    $id = isset($_REQUEST['mb_id']) ? esc(trim($_REQUEST['mb_id'])) : '';
    if($id==='') jres(false,'invalid mb_id');

    $sets = [];
    if(isset($_REQUEST['mb_name'])) $sets[] = "mb_name='".esc(trim($_REQUEST['mb_name']))."'";
    if(isset($_REQUEST['role']))    $sets[] = "role='".esc(trim($_REQUEST['role']))."'";
    if(isset($_REQUEST['class']))   $sets[] = "class=".((string)$_REQUEST['class']===''?'NULL':(int)$_REQUEST['class']);
    if(isset($_REQUEST['mb_hp']))   $sets[] = "mb_hp='".esc(trim($_REQUEST['mb_hp']))."'";
    if(isset($_REQUEST['mb_email']))$sets[] = "mb_email='".esc(trim($_REQUEST['mb_email']))."'";
    if(isset($_REQUEST['mb_sex']))  $sets[] = "mb_sex='".esc(trim($_REQUEST['mb_sex']))."'";

    foreach(['mb_zip1','mb_zip2','mb_addr1','mb_addr2','mb_addr3','mb_addr_jibeon'] as $f){
        if(isset($_REQUEST[$f])) $sets[] = "{$f}='".esc(trim($_REQUEST[$f]))."'";
    }

    if(array_key_exists('product_id', $_REQUEST)) {
        $sets[] = "product_id=".(($_REQUEST['product_id']===''||is_null($_REQUEST['product_id']))?'NULL':(int)$_REQUEST['product_id']);
    }
    if(isset($_REQUEST['product_price']))        $sets[] = "product_price=".(int)$_REQUEST['product_price'];
    if(isset($_REQUEST['product_price_first']))  $sets[] = "product_price_first=".(int)$_REQUEST['product_price_first'];
    if(isset($_REQUEST['product_price_last']))   $sets[] = "product_price_last=".(int)$_REQUEST['product_price_last'];

    if(array_key_exists('mb_leave_date', $_REQUEST)) {
        $sets[] = "mb_leave_date='".esc(trim($_REQUEST['mb_leave_date']))."'"; // ''이면 재학/재직
    }

    if(empty($sets)) jres(false,'nothing to update');

    $ok = sql_query("UPDATE {$table} SET ".implode(',', $sets)." WHERE {$pk}='{$id}'", false);
    if(!$ok) jres(false,'update fail');

    $row = sql_fetch("SELECT mb_id, mb_name, role, class, mb_hp, mb_email, mb_sex,
                             product_id, product_price, product_price_first, product_price_last,
                             mb_datetime, mb_leave_date, mb_today_login, mb_login_ip, mb_level
                      FROM {$table} WHERE {$pk}='{$id}'");
    jres(true,$row);
break;

case 'MEMBER_DELETE':
    $id = isset($_REQUEST['mb_id']) ? esc(trim($_REQUEST['mb_id'])) : '';
    if($id==='') jres(false,'invalid mb_id');
    $ok = sql_query("DELETE FROM {$table} WHERE {$pk}='{$id}'", false);
    if(!$ok) jres(false,'delete fail');
    jres(true,'deleted');
break;

default:
    jres(false,'invalid type');
}
