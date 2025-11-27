<?php

/* ----------------------------------------------------------
 * 리스트
 * ---------------------------------------------------------- */
function select_mock_subject_list($start, $num, $type = '')
{
  $start = (int)$start;
  $num   = (int)$num;
  $type  = trim($type);

  // 기본 where 조건
  $where = "is_deleted = 0";

  // type 값이 있을 때만 필터 적용
  if ($type !== '') {
      $where .= " AND type = '" . esc($type) . "'";
  }

  $sql = "SELECT *
            FROM cn_mock_subject
            WHERE {$where}
            ORDER BY id DESC
            LIMIT {$start}, {$num}";

  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) {
    $list[] = $row;
  }

  return $list;
}


function select_mock_subject_listcnt($type = '')
{
  $type = trim($type);
  $where = "is_deleted = 0";

  if ($type !== '') {
      $where .= " AND type = '" . esc($type) . "'";
  }

  $row = sql_fetch("SELECT COUNT(id) AS cnt
                      FROM cn_mock_subject
                      WHERE {$where}");

  return (int)$row['cnt'];
}



/* ----------------------------------------------------------
 * 단건 조회
 * ---------------------------------------------------------- */
function select_mock_subject_one($id)
{
  $id = (int)$id;

  return sql_fetch("SELECT *
                      FROM cn_mock_subject
                      WHERE id = {$id}
                        AND is_deleted = 0");
}


/* ----------------------------------------------------------
 * mock_id 삭제로 인해 select_mock_subject_by_mock 삭제
 * ---------------------------------------------------------- */


/* ----------------------------------------------------------
 * 등록
 * ---------------------------------------------------------- */
function insert_mock_subject($subject_name, $type)
{
  $subject_name = esc($subject_name);
  $type         = esc($type);

  $sql = "INSERT INTO cn_mock_subject
            SET subject_name = '{$subject_name}',
                type         = '{$type}'";

  return sql_query($sql);
}


/* ----------------------------------------------------------
 * 수정 (선택적 업데이트 스타일)
 * ---------------------------------------------------------- */
function update_mock_subject($id, $subject_name = null, $type = null)
{
  $id = (int)$id;
  $sets = [];

  if (!is_null($subject_name)) {
    $sets[] = "subject_name = '" . esc($subject_name) . "'";
  }

  if (!is_null($type)) {
    $sets[] = "type = '" . esc($type) . "'";
  }

  if (empty($sets)) return true;

  $sql = "UPDATE cn_mock_subject
            SET " . implode(',', $sets) . ",
                mod_dt = NOW()
            WHERE id = {$id}
              AND is_deleted = 0";

  return sql_query($sql);
}


/* ----------------------------------------------------------
 * 물리 삭제 (사용 안 함, soft delete 사용)
 * ---------------------------------------------------------- */
function delete_mock_subject($id)
{
  $id = (int)$id;
  return sql_query("DELETE FROM cn_mock_subject WHERE id = {$id}");
}


/* ----------------------------------------------------------
 * 소프트 삭제
 * ---------------------------------------------------------- */
function soft_delete_mock_subject($id)
{
  $id = (int)$id;

  $sql = "UPDATE cn_mock_subject
            SET is_deleted = 1,
                mod_dt = NOW()
            WHERE id = {$id}";

  return sql_query($sql);
}
