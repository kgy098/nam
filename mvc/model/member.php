<?

function get_member_form_defaults() {
    return [
        'mb_name'        => '',
        'mb_hp'          => '',
        'mb_email'       => '',
        'mb_addr'        => '',
        'gender'         => '',
        'ban'            => '',
        'auth_no'        => '',
        'join_date'      => '',
        'out_date'       => '',
        'product'        => '',
        'price'          => '',
        'first_price'    => '',
        'last_price'     => '',
    ];
}


/* ------------------------------------------------------
 * SELECT (조회)
 * ------------------------------------------------------ */

function select_member_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select *
            from g5_member
            order by mb_no desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_member_listcnt() {
    $row = sql_fetch("select count(mb_no) as cnt from g5_member");
    return $row['cnt'];
}

function select_member_one($mb_no) {
    return sql_fetch("select * from g5_member where mb_no = {$mb_no}");
}

function select_member_by_role($role, $start=0, $num=CN_PAGE_NUM) {
    $sql = "select mb_no, mb_id, mb_name, role, auth_no, mb_hp, mb_email, mb_datetime
            from g5_member
            where role = '{$role}'
            order by mb_no desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

/* 상세 조회 by mb_id */
function select_member_one_by_id($mb_id) {
    $id = trim($mb_id);
    if ($id==='') return null;

    $row = sql_fetch("
      SELECT *
      FROM g5_member
      WHERE mb_id='".sql_escape_string($id)."'
      LIMIT 1
    ");
    return $row ?: null;
}

/* 검색 + 페이징 조회 */
function select_member_list_search($req) {
    global $g5;
    $table = $g5['member_table'] ?? 'g5_member';
    // error_log(__FILE__.__LINE__ . "\n data: " . print_r($req, true));

    // mode: student | teacher
    $mode = $req['mode'] ?? 'student';   // 기본값 student

    $page   = isset($req['page']) ? max(1, (int)$req['page']) : 1;
    $rows   = isset($req['rows']) ? max(1, min(200, (int)$req['rows'])) : 20;
    $offset = ($page - 1) * $rows;

    $field   = $req['field']   ?? '';
    $keyword = $req['keyword'] ?? '';
    $role    = $req['role']    ?? '';   // 외부 role 조건
    $class   = $req['class']   ?? '';
    $left_yn = $req['left_yn'] ?? '';

    $dt_from = $req['dt_from'] ?? ($req['start_date'] ?? '');
    $dt_to   = $req['dt_to']   ?? ($req['end_date'] ?? '');

    if ($dt_from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_from)) $dt_from = '';
    if ($dt_to   !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_to))   $dt_to   = '';

    if ($dt_from && $dt_to && $dt_from > $dt_to) {
        $tmp = $dt_from; $dt_from = $dt_to; $dt_to = $tmp;
    }

    $where = "1";

    /* ---------------------------------------------------------
       1) mode 값으로 role 자동 적용 (핵심)
    --------------------------------------------------------- */
    if ($mode === 'teacher') {
        $where .= " AND role='TEACHER'";
    } else {
        $where .= " AND role='STUDENT'";   // 기본
    }

    /* ---------------------------------------------------------
       2) 추가 검색 조건들 (기존 그대로 유지)
    --------------------------------------------------------- */

    if ($field && $keyword) {
        $f = preg_replace('/[^a-z0-9_]/i', '', $field);
        $k = sql_escape_string($keyword);
        $where .= " AND {$f} LIKE '%{$k}%'";
    }

    if ($role)  $where .= " AND role='" . sql_escape_string($role) . "'";
    if ($class) $where .= " AND class=" . (int)$class;

    if ($left_yn === 'Y') $where .= " AND mb_leave_date<>''";
    if ($left_yn === 'N') $where .= " AND mb_leave_date=''";

    if ($dt_from && $dt_to) {
        $where .= " AND mb_datetime>='{$dt_from} 00:00:00' AND mb_datetime<='{$dt_to} 23:59:59'";
    } else if ($dt_from) {
        $where .= " AND mb_datetime>='{$dt_from} 00:00:00'";
    } else if ($dt_to) {
        $where .= " AND mb_datetime<='{$dt_to} 23:59:59'";
    }

    /* ---------------------------------------------------------
       3) SQL 실행
    --------------------------------------------------------- */

    $cnt = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
    $total = (int)$cnt['cnt'];

    $sql = "
      SELECT *
      FROM {$table}
      WHERE {$where}
      ORDER BY mb_datetime DESC, mb_id DESC
      LIMIT {$offset}, {$rows}
    ";

    // error_log(__FILE__.__LINE__ . "\nSQL: " . $sql);

    $list = [];
    $q = sql_query($sql);
    while ($row = sql_fetch_array($q)) $list[] = $row;

    return ['total' => $total, 'list' => $list, 'page' => $page, 'rows' => $rows];
}


function select_member_excel_list($params = [])
{
    global $g5;
    $table = isset($g5['member_table']) ? $g5['member_table'] : 'g5_member';

    $field   = trim($params['field']   ?? '');
    $keyword = trim($params['keyword'] ?? '');
    $role    = trim($params['role']    ?? '');
    $class   = trim($params['class']   ?? '');
    $left_yn = trim($params['left_yn'] ?? '');

    $dt_from = '';
    $dt_to   = '';

    if (!empty($params['dt_from']))        $dt_from = trim($params['dt_from']);
    else if (!empty($params['start_date'])) $dt_from = trim($params['start_date']);

    if (!empty($params['dt_to']))          $dt_to = trim($params['dt_to']);
    else if (!empty($params['end_date']))   $dt_to = trim($params['end_date']);

    if ($dt_from !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_from)) $dt_from = '';
    if ($dt_to   !== '' && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $dt_to))   $dt_to   = '';

    if ($dt_from !== '' && $dt_to !== '' && $dt_from > $dt_to) {
        $tmp     = $dt_from;
        $dt_from = $dt_to;
        $dt_to   = $tmp;
    }

    $where = "1";

    if ($field !== '' && $keyword !== '') {
        $f = preg_replace('/[^a-z0-9_]/i', '', $field);
        $k = esc($keyword);
        $where .= " AND {$f} LIKE '%{$k}%'";
    }
    if ($role !== '')   $where .= " AND role='" . esc($role) . "'";
    if ($class !== '')  $where .= " AND class=" . (int)$class;
    if ($left_yn === 'Y') $where .= " AND mb_leave_date<>''";
    if ($left_yn === 'N') $where .= " AND mb_leave_date=''";

    if ($dt_from !== '' && $dt_to !== '') {
        $where .= " AND mb_datetime>='" . esc($dt_from) . " 00:00:00' AND mb_datetime<='" . esc($dt_to) . " 23:59:59'";
    } else if ($dt_from !== '') {
        $where .= " AND mb_datetime>='" . esc($dt_from) . " 00:00:00'";
    } else if ($dt_to !== '') {
        $where .= " AND mb_datetime<='" . esc($dt_to) . " 23:59:59'";
    }

    $sql = "
      SELECT mb_id, mb_name, role, class, mb_hp, mb_email,
             product_id, product_price, product_price_first, product_price_last,
             mb_datetime, mb_leave_date
      FROM {$table}
      WHERE {$where}
      ORDER BY mb_datetime DESC, mb_id DESC
    ";
    $result = sql_query($sql);

    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;

    return $list;
}

function select_member_dup($mb_name, $mb_hp)
{
    $sql = "
        SELECT COUNT(*) AS cnt 
        FROM g5_member
        WHERE mb_name = '".esc($mb_name)."'
          AND mb_hp = '".esc($mb_hp)."'
    ";
    $row = sql_fetch($sql);
    return $row['cnt'] > 0;
}


/* ------------------------------------------------------
 * INSERT (추가)
 * ------------------------------------------------------ */
function insert_member_full($req) {
    global $g5;
    $table = $g5['member_table'] ?? 'g5_member';

    // mb_id 생성
    $mb_id = make_new_member_id($req['role'] ?? 'STUDENT');

    // 필수값 체크
    $mb_name = sql_escape_string(trim($req['mb_name'] ?? ''));
    if (!$mb_id || !$mb_name) return ['ok'=>false,'error'=>'required'];

    // 기본 정보
    $role  = sql_escape_string(trim($req['role'] ?? 'STUDENT'));
    $class = (string)($req['class'] ?? '')==='' ? 'NULL' : (int)$req['class'];

    $mb_hp    = sql_escape_string(trim($req['mb_hp']   ?? ''));
    $mb_email = sql_escape_string(trim($req['mb_email']?? ''));
    $mb_sex   = sql_escape_string(trim($req['mb_sex']  ?? ''));

    $mb_zip1  = sql_escape_string(trim($req['mb_zip1'] ?? ''));
    $mb_zip2  = sql_escape_string(trim($req['mb_zip2'] ?? ''));
    $mb_addr1 = sql_escape_string(trim($req['mb_addr1'] ?? ''));
    $mb_addr2 = sql_escape_string(trim($req['mb_addr2'] ?? ''));
    $mb_addr3 = sql_escape_string(trim($req['mb_addr3'] ?? ''));
    $mb_addr_jibeon = sql_escape_string(trim($req['mb_addr_jibeon'] ?? ''));

    // 입실일시 / 퇴실일시
    $join_date = trim($req['join_date'] ?? '');
    $out_date  = trim($req['out_date'] ?? '');

    $join_date_sql = $join_date !== '' ? "'" . sql_escape_string($join_date) . "'" : "NULL";
    $out_date_sql  = $out_date  !== '' ? "'" . sql_escape_string($out_date)  . "'" : "NULL";

    // 상품 항목
    $product_id = isset($req['product_id']) && $req['product_id']!=='' ? (int)$req['product_id'] : 'NULL';

    $product_price        = (int)($req['product_price']        ?? 0);
    $product_price_first  = (int)($req['product_price_first']  ?? 0);
    $product_price_last   = (int)($req['product_price_last']   ?? 0);

    // SQL INSERT
    $sql = "
      INSERT INTO {$table}
      (mb_id, mb_name, role, class, mb_hp, mb_email, mb_sex,
       mb_zip1, mb_zip2, mb_addr1, mb_addr2, mb_addr3, mb_addr_jibeon,
       product_id, product_price, product_price_first, product_price_last,
       mb_datetime, join_date, out_date)
      VALUES
      ('{$mb_id}','{$mb_name}','{$role}',{$class},
       '{$mb_hp}','{$mb_email}','{$mb_sex}',
       '{$mb_zip1}','{$mb_zip2}','{$mb_addr1}','{$mb_addr2}','{$mb_addr3}','{$mb_addr_jibeon}',
       {$product_id},{$product_price},{$product_price_first},{$product_price_last},
       NOW(), {$join_date_sql}, {$out_date_sql})
    ";


    $ok = sql_query($sql,false);
    if (!$ok) return ['ok'=>false,'error'=>'insert fail'];

    $row = sql_fetch("SELECT * FROM {$table} WHERE mb_id='{$mb_id}' LIMIT 1");
    return ['ok'=>true,'data'=>$row];
}


function make_new_member_id($role = 'STUDENT') {
    $prefix = 'S';
    if ($role == 'TEACHER') $prefix = 'T';
    if ($role == 'ADMIN')   $prefix = 'A';

    $row = sql_fetch("SELECT mb_id FROM g5_member 
                      WHERE mb_id LIKE '{$prefix}%' 
                      ORDER BY mb_id DESC LIMIT 1");

    if (!$row) {
        return $prefix . "000001";
    }

    $num = intval(substr($row['mb_id'], 1)) + 1;
    // error_log(__FILE__.__LINE__."\n SQL : " . $num);

    return $prefix . sprintf("%06d", $num);
}


/* ------------------------------------------------------
 * UPDATE (수정)
 * ------------------------------------------------------ */

function update_member_full($req) {
    global $g5;
    $table = $g5['member_table'] ?? 'g5_member';

    $id = sql_escape_string(trim($req['mb_id'] ?? ''));
    if (!$id) return ['ok'=>false,'error'=>'invalid mb_id'];

    $sets = [];

    if (isset($req['mb_name'])) $sets[] = "mb_name='".sql_escape_string(trim($req['mb_name']))."'";
    if (isset($req['role']))    $sets[] = "role='".sql_escape_string(trim($req['role']))."'";

    if (isset($req['class'])) {
        $sets[] = "class=".((string)$req['class']===''?'NULL':(int)$req['class']);
    }

    if (isset($req['mb_hp']))    $sets[] = "mb_hp='".sql_escape_string(trim($req['mb_hp']))."'";
    if (isset($req['mb_email'])) $sets[] = "mb_email='".sql_escape_string(trim($req['mb_email']))."'";
    if (isset($req['mb_sex']))   $sets[] = "mb_sex='".sql_escape_string(trim($req['mb_sex']))."'";

    foreach (['mb_zip1','mb_zip2','mb_addr1','mb_addr2','mb_addr3','mb_addr_jibeon'] as $f) {
        if (isset($req[$f])) $sets[] = "{$f}='".sql_escape_string(trim($req[$f]))."'" ;
    }

    if (array_key_exists('product_id',$req)) {
        $sets[] = "product_id=".(($req['product_id']===''||is_null($req['product_id'])) ? 'NULL' : (int)$req['product_id']);
    }
    if (isset($req['product_price']))       $sets[] = "product_price=".(int)$req['product_price'];
    if (isset($req['product_price_first'])) $sets[] = "product_price_first=".(int)$req['product_price_first'];
    if (isset($req['product_price_last']))  $sets[] = "product_price_last=".(int)$req['product_price_last'];

    if (array_key_exists('mb_leave_date',$req)) {
        $sets[] = "mb_leave_date='".sql_escape_string(trim($req['mb_leave_date']))."'";
    }

    if (empty($sets)) return ['ok'=>false,'error'=>'nothing to update'];

    $sql = "UPDATE {$table} SET ".implode(',', $sets)." WHERE mb_id='{$id}'";
    $ok = sql_query($sql,false);
    if (!$ok) return ['ok'=>false,'error'=>'update fail'];

    $row = sql_fetch("SELECT * FROM {$table} WHERE mb_id='{$id}'");
    return ['ok'=>true,'data'=>$row];
}

/* ------------------------------------------------------
 * DELETE (삭제)
 * ------------------------------------------------------ */

function delete_member_by_id($mb_id) {
    global $g5;
    $table = $g5['member_table'] ?? 'g5_member';

    $id = sql_escape_string(trim($mb_id));
    if (!$id) return ['ok'=>false,'error'=>'invalid mb_id'];

    $ok = sql_query("DELETE FROM {$table} WHERE mb_id='{$id}'",false);
    if (!$ok) return ['ok'=>false,'error'=>'delete fail'];

    return ['ok'=>true,'data'=>'deleted'];
}

?>
