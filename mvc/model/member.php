<?php

/* ==========================================================
   기본값
========================================================== */
function get_member_form_defaults()
{
  return [
    'mb_id'           => '',
    'mb_name'         => '',
    'class'           => '',
    'mb_hp'           => '',
    'mb_email'        => '',
    'mb_addr1'        => '',   // ← 수정됨
    'mb_sex'          => '',
    'auth_no'         => '',
    'join_date'       => '',
    'out_date'        => '',
    'product_id'         => '',
    'product_price'           => '',
    'product_price_first'     => '',
    'product_price_last'      => '',
  ];
}


/* ==========================================================
   1) 단건 조회 (mb_id)
========================================================== */
function select_member_one_by_id($mb_id)
{
  $mb_id = trim($mb_id);
  if ($mb_id === '') return null;

  $sql = "
        SELECT 
            mb_id,
            mb_name,
            role,
            class,
            mb_hp,
            mb_email,
            mb_addr1 ,
            mb_sex,
            auth_no,
            DATE_FORMAT(join_date,'%Y-%m-%d') AS join_date,
            DATE_FORMAT(out_date,'%Y-%m-%d') AS out_date,
            product_id ,
            product_price,
            product_price_first,
            product_price_last,
            mb_datetime
        FROM g5_member
        WHERE mb_id = '{$mb_id}'
        LIMIT 1
    ";
  return sql_fetch($sql);
}


/* ==========================================================
   2) 회원 목록 조회 + 검색 + 페이징
========================================================== */
function select_member_list_search($req)
{
    $table = 'g5_member';

    $mode   = $req['mode'] ?? 'student';
    $page   = isset($req['page']) ? max(1, (int)$req['page']) : 1;
    $rows   = isset($req['rows']) ? max(1, min(200, (int)$req['rows'])) : 20;
    $offset = ($page - 1) * $rows;

    $field      = $req['field'] ?? '';
    $keyword    = $req['keyword'] ?? '';
    $start_date = $req['start_date'] ?? '';
    $end_date   = $req['end_date'] ?? '';

    $where = "1";

    // 역할 자동 결정
    if ($mode === 'teacher') {
        $where .= " AND m.role='TEACHER'";
    } else {
        $where .= " AND m.role='STUDENT'";
    }

    // 검색 조건
    if ($field && $keyword) {
        $f = preg_replace('/[^a-z0-9_]/i', '', $field);
        $k = sql_escape_string($keyword);
        $where .= " AND m.{$f} LIKE '%{$k}%'";
    }

    // 날짜 조건 (mb_datetime 기준 = 가입일)
    if ($start_date && $end_date) {
        $where .= " AND m.mb_datetime >= '{$start_date} 00:00:00'
                    AND m.mb_datetime <= '{$end_date} 23:59:59'";
    } else if ($start_date) {
        $where .= " AND m.mb_datetime >= '{$start_date} 00:00:00'";
    } else if ($end_date) {
        $where .= " AND m.mb_datetime <= '{$end_date} 23:59:59'";
    }

    /* ---------- 총 카운트 ---------- */
    $cnt_sql = "
        SELECT COUNT(*) AS cnt
        FROM g5_member AS m
        WHERE {$where}
    ";
    $cnt = sql_fetch($cnt_sql);
    $total = (int)$cnt['cnt'];

    /* ---------- 리스트 ---------- */
    $sql = "
        SELECT
            m.mb_id,
            m.mb_name,
            m.class,
            m.mb_hp,
            m.mb_email,
            m.product_id,
            m.product_price,
            DATE_FORMAT(m.join_date,'%Y-%m-%d') AS join_date,
            DATE_FORMAT(m.out_date,'%Y-%m-%d') AS out_date,
            DATE_FORMAT(m.mb_datetime,'%Y-%m-%d') AS mb_datetime,
            p.name AS product_name
        FROM g5_member AS m
        LEFT OUTER JOIN cn_product AS p
              ON m.product_id = p.id
        WHERE {$where}
        ORDER BY m.mb_datetime DESC
        LIMIT {$offset}, {$rows}
    ";

    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) {
        $list[] = $row;
    }

    return [
        'total' => $total,
        'list'  => $list,
        'page'  => $page,
        'rows'  => $rows
    ];
}

function select_member_excel_list($req)
{
  // 기존 검색 함수를 그대로 사용
  // 단, rows를 매우 크게 주어 전체 조회
  $req_for_excel = $req;
  $req_for_excel['page'] = 1;
  $req_for_excel['rows'] = 50000;  // 충분히 큰 값

  $result = select_member_list_search($req_for_excel);

  $list = [];

  foreach ($result['list'] as $row) {

    $list[] = [
      'mb_id'        => $row['mb_id'],
      'mb_name'      => $row['mb_name'],
      'role'         => $row['role'],
      'class'        => $row['class'],
      'mb_hp'        => $row['mb_hp'],
      'mb_email'     => $row['mb_email'],

      'product_name'   => $row['product_name']   ?? '',
      'product_price' => $row['product_price'] ?? '',

      // 선납금 / 잔금은 엑셀에서 빈 칸 유지
      'join_date'    => $row['join_date'],
      'out_date'     => $row['out_date'],
    ];
  }

  return $list;
}

/* ==========================================================
   3) 중복 체크 (이름 + 휴대폰)
========================================================== */
function select_member_dup($mb_name, $mb_hp)
{
  $mb_name = sql_escape_string($mb_name);
  $mb_hp   = sql_escape_string($mb_hp);

  $sql = "
        SELECT COUNT(*) AS cnt
        FROM g5_member
        WHERE mb_name = '{$mb_name}'
          AND mb_hp = '{$mb_hp}'
    ";

  $row = sql_fetch($sql);
  return $row['cnt'] > 0;
}


/* ==========================================================
   4) mb_id 생성
========================================================== */
function make_new_member_id($role = 'STUDENT')
{
  if ($role === 'TEACHER') $prefix = 'T';
  else if ($role === 'ADMIN') $prefix = 'A';
  else $prefix = 'S';

  $row = sql_fetch("
        SELECT mb_id 
        FROM g5_member
        WHERE mb_id LIKE '{$prefix}%'
        ORDER BY mb_id DESC
        LIMIT 1
    ");

  if (!$row) {
    return $prefix . "0000001";
  }

  $num = intval(substr($row['mb_id'], 1)) + 1;
  return $prefix . sprintf("%07d", $num);
}


/* ==========================================================
   5) INSERT
========================================================== */
function insert_member_full($req)
{
  $table = 'g5_member';

  /* mb_id 생성 */
  $role  = trim($req['role'] ?? 'STUDENT');
  $mb_id = make_new_member_id($role);

  /* 필수값 */
  $mb_name = trim($req['mb_name'] ?? '');
  if ($mb_name === '') {
    return ['ok' => false, 'error' => 'required'];
  }

  /* 기본 정보 */
  $class = ($req['class'] === '' || !isset($req['class']))
    ? 'NULL'
    : (int)$req['class'];

  $mb_hp     = trim($req['mb_hp'] ?? '');
  $mb_email  = trim($req['mb_email'] ?? '');
  $mb_addr1  = trim($req['mb_addr1'] ?? '');   // ← 수정됨

  /* 성별 */
  $mb_sex = trim($req['mb_sex'] ?? '');

  /* 인증번호 */
  $auth_no = trim($req['auth_no'] ?? '');
  $auth_no_sql = ($auth_no !== '') ? "'{$auth_no}'" : "NULL";

  /* 입실 / 퇴실 */
  $join_date = trim($req['join_date'] ?? '');
  $join_sql  = ($join_date !== '') ? "'{$join_date}'" : "NULL";

  $out_date = trim($req['out_date'] ?? '');
  $out_sql  = ($out_date !== '') ? "'{$out_date}'" : "NULL";

  /* 상품 */
  $product_id = ($req['product_id'] !== '' && isset($req['product_id']))
    ? (int)$req['product_id']
    : 'NULL';

  /* 가격 */
  $product_price = (int)str_replace(',', '', ($req['product_price'] ?? 0));

  $product_price_first = trim($req['product_price_first'] ?? '');
  $first_sql   = ($product_price_first === '') ? "NULL" : (int)str_replace(',', '', $product_price_first);

  $product_price_last = trim($req['product_price_last'] ?? '');
  $last_sql   = ($product_price_last === '') ? "NULL" : (int)str_replace(',', '', $product_price_last);

  /* INSERT */
  $sql = "
        INSERT INTO {$table}
        (
            mb_id, mb_name, role, class,
            mb_hp, mb_email, mb_sex, mb_addr1,
            auth_no,
            product_id, product_price, product_price_first, product_price_last,
            mb_datetime, join_date, out_date
        )
        VALUES
        (
            '{$mb_id}', '{$mb_name}', '{$role}', {$class},
            '{$mb_hp}', '{$mb_email}', '{$mb_sex}', '{$mb_addr1}',
            {$auth_no_sql},
            {$product_id}, {$product_price}, {$first_sql}, {$last_sql},
            NOW(), {$join_sql}, {$out_sql}
        )
    ";

  $ok = sql_query($sql, false);
  if (!$ok) return ['ok' => false, 'error' => 'insert fail'];

  $row = select_member_one_by_id($mb_id);
  return ['ok' => true, 'data' => $row];
}


/* ==========================================================
   6) UPDATE
========================================================== */
function update_member_full($req)
{
  $table = 'g5_member';

  $mb_id = trim($req['mb_id'] ?? '');
  if ($mb_id === '') return ['ok' => false, 'error' => 'invalid mb_id'];

  $sets = [];

  if (isset($req['mb_name']))   $sets[] = "mb_name='" . trim($req['mb_name']) . "'";
  if (isset($req['role']))      $sets[] = "role='" . trim($req['role']) . "'";

  if (isset($req['class'])) {
    $sets[] = "class=" . (trim($req['class']) === '' ? "NULL" : (int)$req['class']);
  }

  if (isset($req['mb_hp']))     $sets[] = "mb_hp='" . trim($req['mb_hp']) . "'";
  if (isset($req['mb_email']))  $sets[] = "mb_email='" . trim($req['mb_email']) . "'";

  /** 여기 수정됨 — mb_addr → mb_addr1 */
  if (isset($req['mb_addr1']))  $sets[] = "mb_addr1='" . trim($req['mb_addr1']) . "'";
  if (isset($req['mb_sex']))  $sets[] = "mb_sex='" . trim($req['mb_sex']) . "'";

  if (isset($req['auth_no'])) {
    $sets[] = "auth_no='" . trim($req['auth_no']) . "'";
  }

  if (isset($req['product_id'])) {
    $sets[] = "product_id=" . ((string)$req['product_id'] === '' ? 'NULL' : (int)$req['product_id']);
  }

  if (isset($req['product_price'])) {
    $sets[] = "product_price=" . (int)str_replace(',', '', $req['product_price']);
  }

  if (isset($req['product_price_first']) && trim($req['product_price_first']) !== '') {
    $sets[] = "product_price_first=" . (int)str_replace(',', '', $req['product_price_first']);
  } else if (isset($req['product_price_first'])) {
    $sets[] = "product_price_first=NULL";
  }

  if (isset($req['product_price_last']) && trim($req['product_price_last']) !== '') {
    $sets[] = "product_price_last=" . (int)str_replace(',', '', $req['product_price_last']);
  } else if (isset($req['product_price_last'])) {
    $sets[] = "product_price_last=NULL";
  }

  if (isset($req['join_date'])) {
    $sets[] = "join_date=" . (($req['join_date'] === '') ? "NULL" : ("'" . trim($req['join_date']) . "'"));
  }

  if (isset($req['out_date'])) {
    $sets[] = "out_date=" . (($req['out_date'] === '') ? "NULL" : ("'" . trim($req['out_date']) . "'"));
  }

  if (empty($sets)) return ['ok' => false, 'error' => 'nothing to update'];

  $sql = "UPDATE {$table} SET " . implode(',', $sets) . " WHERE mb_id='{$mb_id}'";

  $ok = sql_query($sql, false);
  if (!$ok) return ['ok' => false, 'error' => 'update fail'];

  $row = select_member_one_by_id($mb_id);
  return ['ok' => true, 'data' => $row];
}


/* ==========================================================
   7) DELETE
========================================================== */
function delete_member_by_id($mb_id)
{
  $table = 'g5_member';

  $mb_id = trim($mb_id);
  if ($mb_id === '') return ['ok' => false, 'error' => 'invalid mb_id'];

  $ok = sql_query("DELETE FROM {$table} WHERE mb_id='{$mb_id}'", false);
  if (!$ok) return ['ok' => false, 'error' => 'delete fail'];

  return ['ok' => true, 'data' => 'deleted'];
}
