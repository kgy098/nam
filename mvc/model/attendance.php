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

/**
 * 특정 회원의 특정 날짜/출결구분 단건 조회
 * - 날짜 포맷: YYYY-MM-DD
 */
function select_attendance_one_by_key($mb_id, $date, $attend_type_id)
{
  $mb_id          = esc($mb_id);
  $date           = esc($date);
  $attend_type_id = (int)$attend_type_id;

  $sql = "
      SELECT *
      FROM cn_attendance
      WHERE mb_id = '{$mb_id}'
        AND attend_type_id = {$attend_type_id}
        AND DATE(attend_dt) = '{$date}'
      LIMIT 1
  ";

  return sql_fetch($sql);
}

/* ========================================================================
 * Attendance Overview CRUD (조회 전용)
 * 날짜 + 출결구분 기준으로 리스트 생성
 * attendance 데이터가 없어도 미출석으로 출력
 * ======================================================================== */


/**
 * 출결구분 전체 조회
 */
function get_all_attendance_types()
{
  $types = [];

  $sql = "SELECT id, name 
            FROM cn_attendance_type 
            ORDER BY id ASC";

  $res = sql_query($sql);
  while ($row = sql_fetch_array($res)) {
    $types[] = $row;
  }

  return $types;
}


/**
 * 출결 오버뷰 리스트 조회
 * $start = 날짜 offset (며칠 전부터)
 * $num   = 날짜 개수
 */
function select_attendance_overview_list($start, $num, $mb_id = '')
{
  $start = (int)$start;
  $num   = (int)$num;
  $mb_id = trim($mb_id);

  // 출결구분
  $types = get_all_attendance_types();

  $list = [];

  for ($i = 0; $i < $num; $i++) {

    // 기준 날짜
    $day = date('Y-m-d', strtotime('-' . ($start + $i) . ' days'));

    foreach ($types as $t) {

      $row = [
        'date' => $day,
        'attend_type_id' => $t['id'],
        'attend_type_name' => $t['name'],
        'status' => '미출석',
        'attend_dt' => null
      ];

      // 실제 출결 데이터 조회
      $sql = "
                SELECT status, attend_dt
                FROM cn_attendance
                WHERE mb_id = '" . esc($mb_id) . "'
                  AND DATE(attend_dt) = '{$day}'
                  AND attend_type_id = '{$t['id']}'
                LIMIT 1
            ";
      // elog("SQL: $sql");
      $attRow = sql_fetch($sql);

      if ($attRow) {
        $row['status']     = $attRow['status'];
        $row['attend_dt']  = $attRow['attend_dt'];
      }
      // elog("DATA: " . print_r($row, true));

      $list[] = $row;

    }
  }
  // elog("LIST: " . print_r($list, true));

  return $list;
}

/* ================================================================
 * 관리자용 출결 오버뷰 리스트
 * - 날짜 × 반 × 학생 × 출결구분
 * - 실제 데이터가 없어도 "미출석"으로 생성
 * ================================================================ */
function select_attendance_admin_list($start_date, $end_date, $class_id = '', $attend_type_id = '')
{
    // 기본 날짜: 없으면 오늘
    $today = date('Y-m-d');
    $start_date = trim($start_date) === '' ? $today : $start_date;
    $end_date   = trim($end_date)   === '' ? $today : $end_date;

    $start_date = sql_escape_string($start_date);
    $end_date   = sql_escape_string($end_date);

    // 출결구분 전체
    $types = get_all_attendance_types();

    // 특정 출결구분만 필터할 경우
    if ($attend_type_id !== '') {
        $attend_type_id = (int)$attend_type_id;
        $types = array_filter($types, function($t) use ($attend_type_id) {
            return (int)$t['id'] === $attend_type_id;
        });
    }

    // 관리자: 모든 학생 조회
    $member_where = "1=1";

    if ($class_id !== '') {
        $member_where .= " AND m.class = " . intval($class_id);
    }

    $sql_member = "
        SELECT m.mb_id, m.mb_name, c.name AS class_name, m.class
        FROM g5_member AS m
        LEFT JOIN cn_class AS c ON m.class = c.id
        WHERE {$member_where} AND m.role = 'student'
        ORDER BY m.class ASC, m.mb_name ASC
    ";

    $res_members = sql_query($sql_member);
    $members = [];
    while ($row = sql_fetch_array($res_members)) {
        $members[] = $row;
    }

    // 날짜 반복 준비
    $days = [];
    $cur = strtotime($start_date);
    $end = strtotime($end_date);

    while ($cur <= $end) {
        $days[] = date('Y-m-d', $cur);
        $cur = strtotime('+1 day', $cur);
    }

    /* ============================================================
     * 실제 출결 데이터 먼저 조회 (학생 × 날짜 × 출결구분)
     * ============================================================ */
    $att_sql = "
        SELECT 
            a.id,
            a.mb_id,
            DATE(a.attend_dt) AS the_day,
            a.attend_type_id,
            a.status,
            a.attend_dt
        FROM cn_attendance AS a
        WHERE DATE(a.attend_dt) BETWEEN '{$start_date}' AND '{$end_date}'
    ";

    if ($attend_type_id !== '') {
        $att_sql .= " AND a.attend_type_id = {$attend_type_id} ";
    }

    $att_res = sql_query($att_sql);

    $att_map = []; 
    while ($row = sql_fetch_array($att_res)) {
        $key = $row['mb_id'] . '_' . $row['the_day'] . '_' . $row['attend_type_id'];
        $att_map[$key] = $row;
    }

    /* ============================================================
     * 전체 조합 생성 (학생 × 날짜 × 출결구분)
     * ============================================================ */
    $list = [];

    foreach ($members as $m) {
        foreach ($days as $day) {
            foreach ($types as $t) {

                $key = $m['mb_id'] . '_' . $day . '_' . $t['id'];

                // 기본 미출석 템플릿
                $row = [
                    'date'            => $day,
                    'mb_id'           => $m['mb_id'],
                    'mb_name'         => $m['mb_name'],
                    'class_name'      => $m['class_name'],
                    'attend_type_id'  => $t['id'],
                    'attend_type_name'=> $t['name'],
                    'status'          => '미출석',
                    'attend_dt'       => null
                ];

                // 실제 출결 데이터가 있는 경우 업데이트
                if (isset($att_map[$key])) {
                    $attRow = $att_map[$key];
                    $row['status']    = $attRow['status'];
                    $row['attend_dt'] = $attRow['attend_dt'];
                    $row['att_id'] = $attRow['id'];
                }

                $list[] = $row;
            }
        }
    }

    return $list;
}



/**
 * 출석 등록 (2단계: NOW() + 출석완료 고정)
 */
function insert_attendance($mb_id, $attend_type_id, $attend_dt)
{
  $mb_id = sql_escape_string($mb_id);
  $type_id_sql = is_null($attend_type_id) ? "NULL" : intval($attend_type_id);

  $sql = "
        INSERT INTO cn_attendance
        SET mb_id = '{$mb_id}',
            attend_type_id = {$type_id_sql},
            attend_dt = '{$attend_dt}',
            status = '출석완료'
    ";
  // elog("SQL: $sql");
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
  elog("SQL: $sql");

  return sql_query($sql);
}

function delete_attendance($id)
{
  $id = intval($id);
  return sql_query("delete from cn_attendance where id = {$id}");
}
