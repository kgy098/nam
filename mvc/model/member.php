<?

function get_member_form_defaults()
{
  return [
    'mb_name'        => '',
    'class'          => '',
    'mb_hp'          => '',
    'mb_email'       => '',
    'mb_addr'        => '',
    'gender'         => '',
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

function select_member_list($start = 0, $num = CN_PAGE_NUM)
{
  $sql = "select *
            from g5_member
            order by mb_no desc
            limit $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_member_listcnt()
{
  $row = sql_fetch("select count(mb_no) as cnt from g5_member");
  return $row['cnt'];
}

function select_member_one($mb_no)
{
  return sql_fetch("select * from g5_member where mb_no = {$mb_no}");
}

function select_member_by_role($role, $start = 0, $num = CN_PAGE_NUM)
{
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
function select_member_one_by_id($mb_id)
{
  $id = trim($mb_id);
  if ($id === '') return null;

  $row = sql_fetch("
      SELECT *
      FROM g5_member
      WHERE mb_id='" . sql_escape_string($id) . "'
      LIMIT 1
    ");
  return $row ?: null;
}

/* 검색 + 페이징 조회 */
function select_member_list_search($req)
{
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
    $tmp = $dt_from;
    $dt_from = $dt_to;
    $dt_to = $tmp;
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
        WHERE mb_name = '" . esc($mb_name) . "'
          AND mb_hp = '" . esc($mb_hp) . "'
    ";
  error_log(__FILE__ . __LINE__ . "\n SQL: " . $sql);
  $row = sql_fetch($sql);
  return $row['cnt'] > 0;
}


/* ------------------------------------------------------
 * INSERT (추가)
 * ------------------------------------------------------ */
function insert_member_full($req)
{
    global $g5;
    $table = $g5['member_table'] ?? 'g5_member';

    /* -----------------------------------------------------------
       1) mb_id 생성 (총 8자리 / 접두어 + 7자리 숫자)
    ----------------------------------------------------------- */
    $role = trim($req['role'] ?? 'STUDENT');
    $mb_id = make_new_member_id($role);

    /* -----------------------------------------------------------
       2) 필수값
    ----------------------------------------------------------- */
    $mb_name = trim($req['mb_name'] ?? '');
    if (!$mb_id || $mb_name === '') {
        return ['ok' => false, 'error' => 'required'];
    }

    /* -----------------------------------------------------------
       3) 기본 정보 매핑
    ----------------------------------------------------------- */
    $class = ($req['class'] === '' || !isset($req['class']))
           ? 'NULL'
           : (int)$req['class'];

    $mb_hp    = trim($req['mb_hp'] ?? '');
    $mb_email = trim($req['mb_email'] ?? '');

    /* -----------------------------------------------------------
       4) 성별 (HTML은 gender=M/F, DB는 mb_sex=남/여)
    ----------------------------------------------------------- */
    $gender = trim($req['gender'] ?? '');
    if ($gender === 'M') $mb_sex = '남';
    else if ($gender === 'F') $mb_sex = '여';
    else $mb_sex = '';

    /* -----------------------------------------------------------
       5) 주소 (추후 사용 가능)
    ----------------------------------------------------------- */
    $mb_addr = trim($req['mb_addr'] ?? '');

    /* -----------------------------------------------------------
       6) 인증번호 (auth_no)
    ----------------------------------------------------------- */
    $auth_no = trim($req['auth_no'] ?? '');
    $auth_no_sql = $auth_no !== '' ? "'" . $auth_no . "'" : "NULL";

    /* -----------------------------------------------------------
       7) 입실일 / 퇴실일
    ----------------------------------------------------------- */
    $join_date = trim($req['join_date'] ?? '');
    $out_date  = trim($req['out_date'] ?? '');

    $join_date_sql = ($join_date !== '') ? "'" . $join_date . "'" : "NULL";
    $out_date_sql  = ($out_date  !== '') ? "'" . $out_date  . "'" : "NULL";

    /* -----------------------------------------------------------
       8) 상품 (price → product_price)
       ▶ 가격은 "1,200,000" → 1200000 으로 변환
       ▶ first/last 값 없으면 NULL
    ----------------------------------------------------------- */

    // 상품 ID
    $product_id = isset($req['product']) && $req['product'] !== ''
                ? (int)$req['product']
                : 'NULL';

    // 기본 상품 금액
    $product_price = str_replace(',', '', ($req['price'] ?? '0'));
    $product_price = (int)$product_price;

    // 첫달 금액
    if (!isset($req['first_price']) || trim($req['first_price']) === '') {
        $product_price_first_sql = "NULL";
    } else {
        $product_price_first_sql = (int)str_replace(',', '', $req['first_price']);
    }

    // 마지막달 금액
    if (!isset($req['last_price']) || trim($req['last_price']) === '') {
        $product_price_last_sql = "NULL";
    } else {
        $product_price_last_sql = (int)str_replace(',', '', $req['last_price']);
    }

    /* -----------------------------------------------------------
       9) SQL INSERT
    ----------------------------------------------------------- */

    $sql = "
        INSERT INTO {$table}
        (
          mb_id, mb_name, role, class,
          mb_hp, mb_email, mb_sex,
          mb_addr, auth_no,
          product_id, product_price, product_price_first, product_price_last,
          mb_datetime, join_date, out_date
        )
        VALUES
        (
          '{$mb_id}', '{$mb_name}', '{$role}', {$class},
          '{$mb_hp}', '{$mb_email}', '{$mb_sex}',
          '{$mb_addr}', {$auth_no_sql},
          {$product_id}, {$product_price}, {$product_price_first_sql}, {$product_price_last_sql},
          NOW(), {$join_date_sql}, {$out_date_sql}
        )
    ";

    $ok = sql_query($sql, false);
    if (!$ok) {
        return ['ok' => false, 'error' => 'insert fail'];
    }

    $row = sql_fetch("SELECT * FROM {$table} WHERE mb_id='{$mb_id}' LIMIT 1");
    return ['ok' => true, 'data' => $row];
}




function make_new_member_id($role = 'STUDENT')
{
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

  return $prefix . sprintf("%07d", $num);
}


/* ------------------------------------------------------
 * UPDATE (수정)
 * ------------------------------------------------------ */

function update_member_full($req)
{
  global $g5;
  $table = $g5['member_table'] ?? 'g5_member';

  $mb_id = trim($req['mb_id'] ?? '');
  if ($mb_id === '') return ['ok' => false, 'error' => 'invalid mb_id'];

  $sets = [];

  if (isset($req['mb_name']))   $sets[] = "mb_name='" . trim($req['mb_name']) . "'";
  if (isset($req['role']))      $sets[] = "role='" . trim($req['role']) . "'";
  if (isset($req['class']))
    $sets[] = "class=" . (trim($req['class']) === '' ? "NULL" : (int)$req['class']);

  if (isset($req['mb_hp']))     $sets[] = "mb_hp='" . trim($req['mb_hp']) . "'";
  if (isset($req['mb_email']))  $sets[] = "mb_email='" . trim($req['mb_email']) . "'";
  if (isset($req['gender']))    $sets[] = "mb_sex='" . trim($req['gender']) . "'";
  if (isset($req['mb_addr']))   $sets[] = "mb_addr1='" . trim($req['mb_addr']) . "'";

  if (isset($req['product']))
    $sets[] = "product_id=" . ((string)$req['product'] === '' ? 'NULL' : (int)$req['product']);

  if (isset($req['price']))        $sets[] = "product_price=" . (int)$req['price'];
  // 첫달
  if ($req['product_price_first'] === '' || !isset($req['product_price_first'])) {
    $product_price_first_sql = "NULL";
  } else {
    $product_price_first_sql = (int)str_replace(',', '', $req['product_price_first']);
  }

  // 마지막달
  if ($req['product_price_last'] === '' || !isset($req['product_price_last'])) {
    $product_price_last_sql = "NULL";
  } else {
    $product_price_last_sql = (int)str_replace(',', '', $req['product_price_last']);
  }
  if (isset($req['join_date']))
    $sets[] = "join_date=" . (($req['join_date'] === '') ? "NULL" : ("'" . trim($req['join_date']) . "'"));

  if (isset($req['out_date']))
    $sets[] = "out_date=" . (($req['out_date'] === '') ? "NULL" : ("'" . trim($req['out_date']) . "'"));

  if (empty($sets)) return ['ok' => false, 'error' => 'nothing to update'];

  $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE mb_id='{$mb_id}'";

  $ok = sql_query($sql, false);
  if (!$ok) return ['ok' => false, 'error' => 'update fail'];

  $row = sql_fetch("SELECT * FROM {$table} WHERE mb_id='{$mb_id}' LIMIT 1");
  return ['ok' => true, 'data' => $row];
}


/* ------------------------------------------------------
 * DELETE (삭제)
 * ------------------------------------------------------ */

function delete_member_by_id($mb_id)
{
  global $g5;
  $table = $g5['member_table'] ?? 'g5_member';

  $id = trim($mb_id);
  if ($id === '') return ['ok' => false, 'error' => 'invalid mb_id'];

  $ok = sql_query("DELETE FROM {$table} WHERE mb_id='{$id}'", false);
  if (!$ok) return ['ok' => false, 'error' => 'delete fail'];

  return ['ok' => true, 'data' => 'deleted'];
}
