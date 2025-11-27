<?php

function select_attendance_list($start = 0, $num = CN_PAGE_NUM)
{
  $start = intval($start);
  $num   = intval($num);

  $sql = "select *
            from cn_attendance
            order by attend_dt desc, id desc
            limit {$start}, {$num}";
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) {
    $list[] = $row;
  }

  return $list;
}

function select_attendance_listcnt()
{
  $row = sql_fetch("select count(id) as cnt from cn_attendance");
  return isset($row['cnt']) ? intval($row['cnt']) : 0;
}

function select_attendance_one($id)
{
  $id = intval($id);
  return sql_fetch("select * from cn_attendance where id = {$id}");
}

function select_attendance_by_student($mb_id, $start = 0, $num = CN_PAGE_NUM)
{
  $mb_id = sql_escape_string($mb_id);
  $start = intval($start);
  $num   = intval($num);

  $sql = "select *
            from cn_attendance
            where mb_id = '{$mb_id}'
            order by attend_dt desc, id desc
            limit {$start}, {$num}";
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) {
    $list[] = $row;
  }

  return $list;
}

/**
 * 기간별 조회 (type 컬럼 제거 반영)
 * - $mb_id, $status는 옵션 필터
 */
function select_attendance_between($from_dt, $to_dt, $mb_id = null, $status = null, $start = 0, $num = CN_PAGE_NUM)
{
  $from_dt = sql_escape_string($from_dt);
  $to_dt   = sql_escape_string($to_dt);
  $start   = intval($start);
  $num     = intval($num);

  $where = "attend_dt between '{$from_dt}' and '{$to_dt}'";

  if (!is_null($mb_id) && $mb_id !== '') {
    $mb_id  = sql_escape_string($mb_id);
    $where .= " and mb_id = '{$mb_id}'";
  }

  if (!is_null($status) && $status !== '') {
    $status = sql_escape_string($status);
    $where .= " and status = '{$status}'";
  }

  $sql = "select *
            from cn_attendance
            where {$where}
            order by attend_dt desc, id desc
            limit {$start}, {$num}";
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) {
    $list[] = $row;
  }

  return $list;
}

/**
 * 출석 등록 (2단계: NOW() + 출석완료 고정)
 */
function insert_attendance($mb_id, $attend_type_id)
{
    $mb_id = sql_escape_string($mb_id);
    $type_id_sql = is_null($attend_type_id) ? "NULL" : intval($attend_type_id);

    $sql = "
        INSERT INTO cn_attendance
        SET mb_id = '{$mb_id}',
            attend_type_id = {$type_id_sql},
            attend_dt = NOW(),
            status = '출석완료'
    ";

    return sql_query($sql);
}


/**
 * 단건 update
 * - type 컬럼 삭제 -> 관련 처리 제거
 */
function update_attendance($id, $attend_type_id = null, $attend_dt = null, $status = null)
{
  $id = intval($id);

  $sets = [];

  if (!is_null($attend_type_id)) {
    $sets[] = "attend_type_id = " . intval($attend_type_id);
  }

  if (!is_null($attend_dt)) {
    $attend_dt = sql_escape_string($attend_dt);
    $sets[] = "attend_dt = '{$attend_dt}'";
  }

  if (!is_null($status)) {
    $status = sql_escape_string($status);
    $sets[] = "status = '{$status}'";
  }

  if (empty($sets)) {
    return true;
  }

  $sql = "update cn_attendance
            set " . implode(', ', $sets) . "
            where id = {$id}";

  return sql_query($sql);
}

function delete_attendance($id)
{
  $id = intval($id);
  return sql_query("delete from cn_attendance where id = {$id}");
}

/* ===========================================================
   출결현황용: 학생(회원) + 반 정보와 OUTER JOIN
   - 기준: g5_member(학생) 기준으로 LEFT JOIN cn_attendance
   - 같은 날짜에 출결이 없으면 attendance 컬럼이 null 로 조회
   - 필요하면 반(class) 기준 필터 가능 (g5_member.class 가정)
   =========================================================== */

/**
 * 특정 날짜의 출결현황 리스트
 *  - $attend_date: 'YYYY-MM-DD' (날짜 기준)
 *  - $class: 해당 반만 보고 싶을 때 (옵션)
 *  - $attend_type_id: 오전/점심후 등 타입 필터 (옵션)
 */
function select_attendance_status_by_date($attend_date, $class = null, $attend_type_id = null, $start = 0, $num = CN_PAGE_NUM)
{
  $attend_date = sql_escape_string($attend_date);
  $start       = intval($start);
  $num         = intval($num);

  $where = "m.role = 'STUDENT'";

  if (!is_null($class) && $class !== '') {
    $class = intval($class);
    $where   .= " and m.class = {$class}";
  }

  $join_cond = "a.mb_id = m.mb_id
                  and date(a.attend_dt) = '{$attend_date}'";

  if (!is_null($attend_type_id)) {
    $attend_type_id = intval($attend_type_id);
    $join_cond .= " and a.attend_type_id = {$attend_type_id}";
  }

  $sql = "select
            m.mb_no,
            m.mb_id,
            m.mb_name,
            m.class as class,
            a.id as attendance_id,
            a.attend_type_id,
            a.attend_dt,
            a.status,
            t.name as attend_type_name
        from g5_member m
        left join cn_attendance a
               on {$join_cond}
        left join cn_attendance_type t
               on t.id = a.attend_type_id
        where {$where}
        order by m.class, m.mb_no
        limit {$start}, {$num}";


  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) {
    $list[] = $row;
  }

  return $list;
}

function select_attendance_status_by_date_cnt($attend_date, $class = null, $attend_type_id = null)
{
  $attend_date = sql_escape_string($attend_date);

  $where = "m.role = 'STUDENT'";

  if (!is_null($class) && $class !== '') {
    $class = intval($class);
    $where   .= " and m.class = {$class}";
  }

  $join_cond = "a.mb_id = m.mb_id
                  and date(a.attend_dt) = '{$attend_date}'";

  if (!is_null($attend_type_id)) {
    $attend_type_id = intval($attend_type_id);
    $join_cond .= " and a.attend_type_id = {$attend_type_id}";
  }

  $sql = "select count(*) as cnt
            from g5_member m
            left join cn_attendance a
                   on {$join_cond}
            where {$where}";

  $row = sql_fetch($sql);
  return isset($row['cnt']) ? intval($row['cnt']) : 0;
}


/**
 * 출결현황 리스트
 * 학생 × 출결구분 전체 조합 + 기간출결 LEFT JOIN
 *
 * @param string $start_date  YYYY-MM-DD
 * @param string $end_date    YYYY-MM-DD
 * @param int|null $class
 * @param int|null $attend_type_id
 * @param int $start
 * @param int $num
 */
function select_attendance_status($start_date, $end_date, $class = null, $attend_type_id = null, $start = 0, $num = CN_PAGE_NUM)
{
  $start_date = sql_escape_string($start_date);
  $end_date   = sql_escape_string($end_date);
  $start      = intval($start);
  $num        = intval($num);

  $where = "m.role = 'STUDENT'";

  if (!is_null($class) && $class !== '') {
    $class = intval($class);
    $where   .= " AND m.class = {$class}";
  }

  if (!is_null($attend_type_id) && $attend_type_id !== '') {
    $attend_type_id = intval($attend_type_id);
    $where          .= " AND t.id = {$attend_type_id}";
  }

  // CROSS JOIN + LEFT JOIN 출결 OUTER JOIN → 화면설계서 100% 일치
  $sql = "
        SELECT
            m.mb_no,
            m.mb_id,
            m.mb_name,
            m.class,
            t.id AS attend_type_id,
            t.name AS attend_type_name,
            a.id        AS attendance_id,
            a.attend_dt,
            a.status
        FROM g5_member m
        CROSS JOIN cn_attendance_type t
        LEFT JOIN cn_attendance a
            ON a.mb_id = m.mb_id
           AND a.attend_type_id = t.id
           AND a.attend_dt BETWEEN '{$start_date} 00:00:00' AND '{$end_date} 23:59:59'
        WHERE {$where}
        ORDER BY m.class, m.mb_no, t.id
        LIMIT {$start}, {$num}
    ";

  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) {
    $list[] = $row;
  }

  return $list;
}


/**
 * 출결현황 카운트
 * 학생 × 출결구분 전체 개수 계산
 */
function select_attendance_status_cnt($start_date, $end_date, $class = null, $attend_type_id = null)
{
  $start_date = sql_escape_string($start_date);
  $end_date   = sql_escape_string($end_date);

  $where = "m.role = 'STUDENT'";

  if (!is_null($class) && $class !== '') {
    $class = intval($class);
    $where   .= " AND m.class = {$class}";
  }

  if (!is_null($attend_type_id) && $attend_type_id !== '') {
    $attend_type_id = intval($attend_type_id);
    $where          .= " AND t.id = {$attend_type_id}";
  }

  // 학생 × 출결구분 개수 계산
  $sql = "
        SELECT COUNT(*) AS cnt
        FROM g5_member m
        CROSS JOIN cn_attendance_type t
        WHERE {$where}
    ";

  $row = sql_fetch($sql);
  return isset($row['cnt']) ? intval($row['cnt']) : 0;
}
