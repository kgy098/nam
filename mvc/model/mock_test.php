<?php

/* ============================================================
   모의고사 리스트
   params: start, rows, status, sdate, edate
============================================================ */
function select_mock_test_list($params = []) {
    $start = intval($params['start'] ?? 0);
    $rows  = intval($params['rows'] ?? 20);

    $status = $params['status'] ?? '';
    $sdate  = $params['sdate'] ?? '';
    $edate  = $params['edate'] ?? '';

    $where = " WHERE 1=1 ";

    if ($status !== '') 
        $where .= " AND status = '".sql_real_escape_string($status)."' ";

    if ($sdate) 
        $where .= " AND exam_date >= '".sql_real_escape_string($sdate)."' ";

    if ($edate) 
        $where .= " AND exam_date <= '".sql_real_escape_string($edate)."' ";

    $sql = "
        SELECT *
        FROM cn_mock_test
        $where
        ORDER BY exam_date DESC, id DESC
        LIMIT $start, $rows
    ";

    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;

    return $list;
}

function select_mock_test_listcnt($params = []) {

    $status = $params['status'] ?? '';
    $sdate  = $params['sdate'] ?? '';
    $edate  = $params['edate'] ?? '';

    $where = " WHERE 1=1 ";

    if ($status !== '') 
        $where .= " AND status = '".sql_real_escape_string($status)."' ";

    if ($sdate) 
        $where .= " AND exam_date >= '".sql_real_escape_string($sdate)."' ";

    if ($edate) 
        $where .= " AND exam_date <= '".sql_real_escape_string($edate)."' ";

    $sql = "
        SELECT COUNT(*) AS cnt
        FROM cn_mock_test
        $where
    ";

    $row = sql_fetch($sql);
    return (int)($row['cnt'] ?? 0);
}


/* ============================================================
   모의고사 단건 조회
============================================================ */
function select_mock_test_one($id) {
    $id = intval($id);
    $sql = "
        SELECT *
        FROM cn_mock_test
        WHERE id = '{$id}'
        LIMIT 1
    ";
    return sql_fetch($sql);
}


/* ============================================================
   모의고사 등록
============================================================ */
function insert_mock_test($data = []) {

    $name        = sql_real_escape_string($data['name'] ?? '');
    $description = sql_real_escape_string($data['description'] ?? '');

    $apply_start = $data['apply_start'] ?? null;
    $apply_end   = $data['apply_end'] ?? null;

    $exam_date   = $data['exam_date'] ?? null;
    $status      = sql_real_escape_string($data['status'] ?? '접수중');

    $sql = "
        INSERT INTO cn_mock_test
        SET
            name        = '{$name}',
            description = '{$description}',
            apply_start = " . ($apply_start ? "'" . sql_real_escape_string($apply_start) . "'" : "NULL") . ",
            apply_end   = " . ($apply_end ? "'" . sql_real_escape_string($apply_end) . "'" : "NULL") . ",
            exam_date   = " . ($exam_date ? "'" . sql_real_escape_string($exam_date) . "'" : "NULL") . ",
            status      = '{$status}',
            reg_dt      = NOW()
    ";

    sql_query($sql);
    return sql_insert_id();
}


/* ============================================================
   모의고사 수정
============================================================ */
function update_mock_test($id, $data = []) {
    $id = intval($id);

    $set = [];

    if (isset($data['name']))
        $set[] = " name = '" . sql_real_escape_string($data['name']) . "' ";

    if (isset($data['description']))
        $set[] = " description = '" . sql_real_escape_string($data['description']) . "' ";

    if (isset($data['apply_start']))
        $set[] = " apply_start = " . ($data['apply_start'] ? "'" . sql_real_escape_string($data['apply_start']) . "'" : "NULL");

    if (isset($data['apply_end']))
        $set[] = " apply_end = " . ($data['apply_end'] ? "'" . sql_real_escape_string($data['apply_end']) . "'" : "NULL");

    if (isset($data['exam_date']))
        $set[] = " exam_date = " . ($data['exam_date'] ? "'" . sql_real_escape_string($data['exam_date']) . "'" : "NULL");

    if (isset($data['status']))
        $set[] = " status = '" . sql_real_escape_string($data['status']) . "' ";

    if (!$set) return false;

    $sql = "
        UPDATE cn_mock_test
        SET " . implode(',', $set) . ",
            mod_dt = NOW()
        WHERE id = '{$id}'
    ";

    return sql_query($sql);
}



/* ============================================================
   모의고사 삭제
============================================================ */
function delete_mock_test($id) {
    $id = intval($id);
    $sql = "DELETE FROM cn_mock_test WHERE id = '{$id}' ";
    return sql_query($sql);
}

?>
