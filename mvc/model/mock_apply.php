<?php

/* ============================================================
   모의고사 응시현황 리스트
   - mock_id / subject_id / class_id / status / 기간 검색 지원
   - member/class/mock_test/mock_subject 조인
============================================================ */
function select_mock_apply_list($params = [])
{
  $start      = intval($params['start'] ?? 0);
  $rows       = intval($params['rows'] ?? 20);

  $mock_id    = $params['mock_id'] ?? '';
  $subject_id = $params['subject_id'] ?? '';
  $class_id   = $params['class_id'] ?? '';
  $status     = $params['status'] ?? '';
  $sdate      = $params['sdate'] ?? '';
  $edate      = $params['edate'] ?? '';

  // 학생 기준 where
  $where = " WHERE 1=1 ";

  // 반
  if ($class_id !== '' && $class_id !== null)
    $where .= " AND m.class = '{$class_id}' ";

  // 응시 상태(신청/취소) 필터
  // -> 이 값이 있을 때만 apply 상태로 필터 (없으면 전체 학생 + 미신청 포함)
  if ($status !== '' && $status !== null)
    $where .= " AND a.status = '{$status}' ";

  // apply 쪽 조건은 LEFT JOIN 쪽으로 이동
  $join_apply = " LEFT JOIN cn_mock_apply AS a
                      ON a.mb_id = m.mb_id ";

  // 시험
  if ($mock_id !== '' && $mock_id !== null)
    $join_apply .= " AND a.mock_id = '{$mock_id}' ";

  // 과목
  if ($subject_id !== '' && $subject_id !== null)
    $join_apply .= " AND a.subject_id = '{$subject_id}' ";

  // 기간 (접수 시작/종료 기준)
  if ($sdate !== '')
    $join_apply .= " AND a.apply_start >= '{$sdate} 00:00:00' ";

  if ($edate !== '')
    $join_apply .= " AND a.apply_end <= '{$edate} 23:59:59' ";

  $sql = "
        SELECT 
            a.*,
            m.mb_name,
            c.name AS class_name,
            mt.name AS mock_name,
            mt.exam_date,
            ms.subject_name
        FROM g5_member AS m
        {$join_apply}
        LEFT JOIN cn_class AS c ON m.class = c.id
        LEFT JOIN cn_mock_test AS mt ON a.mock_id = mt.id
        LEFT JOIN cn_mock_subject AS ms ON a.subject_id = ms.id
        {$where} AND m.role='STUDENT' AND ms.type='모의고사과목'
        ORDER BY a.id DESC
        LIMIT {$start}, {$rows}
    ";

  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;

  return $list;
}


/* ============================================================
   모의고사 응시현황 리스트 개수
============================================================ */
function select_mock_apply_listcnt($params = [])
{
  $mock_id    = $params['mock_id'] ?? '';
  $subject_id = $params['subject_id'] ?? '';
  $class_id   = $params['class_id'] ?? '';
  $status     = $params['status'] ?? '';
  $sdate      = $params['sdate'] ?? '';
  $edate      = $params['edate'] ?? '';

  $where = " WHERE 1=1 ";

  if ($class_id !== '' && $class_id !== null)
    $where .= " AND m.class = '{$class_id}' ";

  if ($status !== '' && $status !== null)
    $where .= " AND a.status = '{$status}' ";

  $join_apply = " LEFT JOIN cn_mock_apply AS a
                      ON a.mb_id = m.mb_id ";

  if ($mock_id !== '' && $mock_id !== null)
    $join_apply .= " AND a.mock_id = '{$mock_id}' ";

  if ($subject_id !== '' && $subject_id !== null)
    $join_apply .= " AND a.subject_id = '{$subject_id}' ";

  if ($sdate !== '')
    $join_apply .= " AND a.apply_start >= '{$sdate} 00:00:00' ";

  if ($edate !== '')
    $join_apply .= " AND a.apply_end <= '{$edate} 23:59:59' ";

  $sql = "
        SELECT COUNT(*) AS cnt
        FROM g5_member AS m
        {$join_apply}
        LEFT JOIN cn_class AS c ON m.class = c.id
        LEFT JOIN cn_mock_test AS mt ON a.mock_id = mt.id
        LEFT JOIN cn_mock_subject AS ms ON a.subject_id = ms.id
        {$where} AND m.role='STUDENT' AND ms.type='모의고사과목'
    ";

  $row = sql_fetch($sql);
  return (int)($row['cnt'] ?? 0);
}

function select_mock_apply_teacher_summary(
  $mock_id,
  $class_id,
  $subject_id,
  $status,
  $sdate,
  $edate
) {
  $where = "1=1";

  if ($mock_id !== '')
    $where .= " AND mt.id = '{$mock_id}' ";

  if ($class_id !== '')
    $where .= " AND m.class = '{$class_id}' ";

  if ($subject_id !== '')
    $where .= " AND ms.id = '{$subject_id}' ";

  // 시험일 기간
  if ($sdate !== '')
    $where .= " AND mt.exam_date >= '{$sdate}' ";

  if ($edate !== '')
    $where .= " AND mt.exam_date <= '{$edate}' ";

  // ---------------------------------------------------
  // LEFT JOIN: 신청 안해도 학생이 목록에 나타나야 함
  // ---------------------------------------------------
  $sql = "
        SELECT 
            SUM(CASE WHEN a.status = '신청' THEN 1 ELSE 0 END) AS total_complete,
            SUM(CASE WHEN a.status IS NULL OR a.status <> '신청' THEN 1 ELSE 0 END) AS total_incomplete,
            COUNT(*) AS total
        FROM g5_member AS m
        JOIN cn_mock_test mt ON 1=1
        JOIN cn_mock_subject ms ON 1=1
        LEFT JOIN cn_mock_apply a 
               ON a.mb_id = m.mb_id
              AND a.mock_id = mt.id
              AND a.subject_id = ms.id
        WHERE {$where} AND m.role='STUDENT' AND ms.type='모의고사과목'
    ";

  return sql_fetch($sql);
}

function select_mock_apply_teacher_listcnt(
  $mock_id,
  $class_id,
  $subject_id,
  $status,
  $sdate,
  $edate
) {
  $where = "1=1";

  if ($mock_id !== '')
    $where .= " AND mt.id = '{$mock_id}' ";

  if ($class_id !== '')
    $where .= " AND m.class = '{$class_id}' ";

  if ($subject_id !== '')
    $where .= " AND ms.id = '{$subject_id}' ";

  if ($sdate !== '')
    $where .= " AND mt.exam_date >= '{$sdate}' ";

  if ($edate !== '')
    $where .= " AND mt.exam_date <= '{$edate}' ";

  // 응시여부 필터
  if ($status === 'COMPLETE')       $where .= " AND a.status = '신청' ";
  else if ($status === 'INCOMPLETE') $where .= " AND (a.status IS NULL OR a.status <> '신청') ";

  $sql = "
        SELECT COUNT(*) AS cnt
        FROM g5_member AS m
        JOIN cn_mock_test mt ON 1=1
        JOIN cn_mock_subject ms ON 1=1
        LEFT JOIN cn_mock_apply a 
               ON a.mb_id = m.mb_id
              AND a.mock_id = mt.id
              AND a.subject_id = ms.id
        WHERE {$where} AND m.role='STUDENT' AND ms.type='모의고사과목'
    ";

  $row = sql_fetch($sql);
  return (int)$row['cnt'];
}
function select_mock_apply_teacher_list(
  $start,
  $rows,
  $mock_id,
  $class_id,
  $subject_id,
  $status,
  $sdate,
  $edate
) {
  $where = "1=1";

  if ($mock_id !== '')
    $where .= " AND mt.id = '{$mock_id}' ";

  if ($class_id !== '')
    $where .= " AND m.class = '{$class_id}' ";

  if ($subject_id !== '')
    $where .= " AND ms.id = '{$subject_id}' ";

  if ($sdate !== '')
    $where .= " AND mt.exam_date >= '{$sdate}' ";

  if ($edate !== '')
    $where .= " AND mt.exam_date <= '{$edate}' ";

  if ($status === 'COMPLETE')       $where .= " AND a.status = '신청' ";
  else if ($status === 'INCOMPLETE') $where .= " AND (a.status IS NULL OR a.status <> '신청') ";

  $sql = "
        SELECT 
            mt.name AS mock_name,
            ms.subject_name,
            mt.exam_date,
            m.class AS class_id,
            (SELECT name FROM cn_class WHERE id = m.class LIMIT 1) AS class_name,
            m.mb_name,
            m.mb_id,
            a.status
        FROM g5_member AS m
        JOIN cn_mock_test mt ON 1=1
        JOIN cn_mock_subject ms ON 1=1
        LEFT JOIN cn_mock_apply a 
               ON a.mb_id = m.mb_id
              AND a.mock_id = mt.id
              AND a.subject_id = ms.id
        WHERE {$where} AND m.role='STUDENT' AND ms.type='모의고사과목'
        ORDER BY mt.exam_date DESC, m.mb_name ASC
        LIMIT {$start}, {$rows}
    ";

  $rs = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($rs)) {
    $list[] = $row;
  }
  return $list;
}

/* ============================================================
   학생 전용: 자신의 신청 목록만 조회
============================================================ */
function select_mock_apply_my_list($mb_id)
{
  $mb_id = sql_real_escape_string($mb_id);

  $sql = "
        SELECT 
            a.*,
            mt.name AS mock_name,
            ms.subject_name
        FROM cn_mock_apply AS a
        LEFT JOIN cn_mock_test AS mt ON a.mock_id = mt.id
        LEFT JOIN cn_mock_subject AS ms ON a.subject_id = ms.id
        WHERE a.mb_id = '{$mb_id}'
        ORDER BY a.id DESC
    ";

  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_mock_apply_my_listcnt($mb_id)
{
  $mb_id = sql_real_escape_string($mb_id);

  $sql = "
        SELECT COUNT(*) AS cnt
        FROM cn_mock_apply AS a
        WHERE a.mb_id = '{$mb_id}'
    ";

  $row = sql_fetch($sql);
  return (int)($row['cnt'] ?? 0);
}

/* ============================================================
   학생 전용: 모의고사 + 과목 + 신청현황 조합 API
============================================================ */
/* ============================================================
   학생용 모의고사 신청 현황 (페이징)
   기존 CRUD 재사용 (새 SQL 불필요)
============================================================ */
function select_mock_apply_my_overview_list($mb_id, $start, $rows)
{

  $mb_id = sql_real_escape_string($mb_id);

  // 1) 페이징된 모의고사 목록
  $tests = select_mock_test_list([
    'start' => $start,
    'rows'  => $rows
  ]);

  // 2) 전체 모의고사 과목(모의고사과목만)
  $subjects = select_mock_subject_list(0, 9999, '모의고사과목');

  // 3) 내 신청 목록 map
  $apply_map = [];
  $res = select_mock_apply_my_list($mb_id);

  // elog("DATA: " . print_r($res, true));

  foreach ($res as $row) {
    $apply_map[$row['mock_id']][$row['subject_id']] = $row['status'];
  }

  // 4) 조합
  $output = [];
  $now = date('Y-m-d H:i:s');

  foreach ($tests as $mock) {

    // 접수 가능 여부
    $can_apply = true;

    if ($mock['status'] !== '접수중') $can_apply = false;
    if ($mock['apply_start'] && $now < $mock['apply_start']) $can_apply = false;
    if ($mock['apply_end']   && $now > $mock['apply_end'])   $can_apply = false;

    // 과목별 조합
    $subject_list = [];
    foreach ($subjects as $sub) {

      $sid = $sub['id'];
      $applied = (
        isset($apply_map[$mock['id']][$sid]) &&
        $apply_map[$mock['id']][$sid] === '신청'
      );

      $subject_list[] = [
        'id'           => $sub['id'],
        'subject_name' => $sub['subject_name'],
        'type'         => $sub['type'],
        'applied'      => $applied
      ];
    }

    $output[] = [
      'mock'      => $mock,
      'subjects'  => $subject_list,
      'can_apply' => $can_apply
    ];
  }

  return $output;
}

function select_mock_apply_my_overview_listcnt($mb_id)
{
  // 모의고사 전체 개수만 반환
  return select_mock_test_listcnt([]);
}

/* ============================================================
   학생 전용: 특정 시험에서 과목별 신청 여부 조회
============================================================ */
function select_mock_apply_my_status($mock_id, $mb_id)
{

  $mock_id = intval($mock_id);
  $mb_id   = sql_real_escape_string($mb_id);

  $sql = "
        SELECT subject_id, status
        FROM cn_mock_apply
        WHERE mock_id = '{$mock_id}'
          AND mb_id   = '{$mb_id}'
    ";

  $result = sql_query($sql);
  $map = [];

  while ($row = sql_fetch_array($result)) {
    $map[$row['subject_id']] = $row['status']; // '신청' or '취소'
  }

  return $map;
}

/* ============================================================
   학생 전용: 신청 / 취소 토글
   - 이미 신청 → 취소로 변경
   - 미신청 → 신청으로 생성 or status 변경
============================================================ */
function toggle_mock_apply($mock_id, $subject_id, $mb_id)
{

  $mock_id    = intval($mock_id);
  $subject_id = intval($subject_id);
  $mb_id      = sql_real_escape_string($mb_id);

  // 1) 시험 정보 (접수기간, 상태 체크)
  $mock = select_mock_test_one($mock_id);
  if (!$mock) {
    return ['result' => 'FAIL', 'msg' => '시험 정보를 찾을 수 없습니다.'];
  }

  $now = date('Y-m-d H:i:s');

  // 접수기간 체크
  if ($mock['apply_start'] && $now < $mock['apply_start']) {
    return ['result' => 'FAIL', 'msg' => '접수 시작 전입니다.'];
  }

  if ($mock['apply_end'] && $now > $mock['apply_end']) {
    return ['result' => 'FAIL', 'msg' => '접수 기간이 마감되었습니다.'];
  }

  // 상태 체크
  if ($mock['status'] !== '접수중') {
    return ['result' => 'FAIL', 'msg' => '현재 접수할 수 없는 상태입니다.'];
  }

  // 2) 기존 신청 여부 확인
  $sql = "
        SELECT id, status
        FROM cn_mock_apply
        WHERE mock_id = '{$mock_id}'
          AND subject_id = '{$subject_id}'
          AND mb_id = '{$mb_id}'
        LIMIT 1
    ";
  $exist = sql_fetch($sql);

  // 3) 신규 신청 (없으면 insert)
  if (!$exist) {
    $id = insert_mock_apply([
      'mock_id'    => $mock_id,
      'mb_id'      => $mb_id,
      'subject_id' => $subject_id,
      'status'     => '신청'
    ]);

    return ['result' => 'SUCCESS', 'mode' => '신청', 'id' => $id];
  }

  // 4) 토글 (신청 → 취소 / 취소 → 신청)
  $new_status = ($exist['status'] === '신청') ? '취소' : '신청';

  update_mock_apply($exist['id'], ['status' => $new_status]);

  return ['result' => 'SUCCESS', 'mode' => $new_status];
}

/* ============================================================
   단건 조회
============================================================ */
function select_mock_apply_one($id)
{
  $id = intval($id);

  $sql = "
        SELECT 
            a.*,
            m.mb_name,
            c.name as class_name,
            mt.name AS mock_name,
            ms.subject_name
        FROM cn_mock_apply AS a
        JOIN g5_member AS m ON a.mb_id = m.mb_id
        LEFT JOIN cn_class AS c ON m.class = c.id
        LEFT JOIN cn_mock_test AS mt ON a.mock_id = mt.id
        LEFT JOIN cn_mock_subject AS ms ON a.subject_id = ms.id
        WHERE a.id = '{$id}'
        LIMIT 1
    ";

  return sql_fetch($sql);
}

/* ============================================================
   등록
============================================================ */
function insert_mock_apply($data = [])
{

  $mock_id    = $data['mock_id'];
  $mb_id      = $data['mb_id'];
  $subject_id = $data['subject_id'] ?? null;
  $status     = $data['status'] ?? '신청';

  $sql = "
        INSERT INTO cn_mock_apply
        SET 
            mock_id    = '{$mock_id}',
            mb_id      = '{$mb_id}',
            subject_id = " . ($subject_id ? "'{$subject_id}'" : "NULL") . ",
            status     = '{$status}',
            reg_dt     = NOW()
    ";

  sql_query($sql);
  return sql_insert_id();
}

/* ============================================================
   수정
============================================================ */
function update_mock_apply($id, $data = [])
{
  $id = intval($id);

  $set = [];

  if (isset($data['mock_id']))
    $set[] = " mock_id = '{$data['mock_id']}' ";

  if (isset($data['mb_id']))
    $set[] = " mb_id = '{$data['mb_id']}' ";

  if (isset($data['subject_id']))
    $set[] = " subject_id = " . ($data['subject_id'] ? "'{$data['subject_id']}'" : "NULL");

  if (isset($data['status']))
    $set[] = " status = '{$data['status']}' ";

  if (!$set) return false;

  $sql = "
        UPDATE cn_mock_apply
        SET " . implode(',', $set) . ",
            mod_dt = NOW()
        WHERE id = '{$id}'
    ";

  return sql_query($sql);
}

/* ============================================================
   삭제
============================================================ */
function delete_mock_apply($id)
{
  $id = intval($id);
  $sql = "DELETE FROM cn_mock_apply WHERE id = '{$id}' ";
  return sql_query($sql);
}
