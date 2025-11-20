<?php

/* ============================================================
   모의고사 응시현황 리스트
   - mock_id / subject_id / class_id / status / 기간 검색 지원
   - member/class/mock_test/mock_subject 조인
============================================================ */
function select_mock_apply_list($params = []) {

    $start      = intval($params['start'] ?? 0);
    $rows       = intval($params['rows'] ?? 20);

    $mock_id    = $params['mock_id'] ?? '';
    $subject_id = $params['subject_id'] ?? '';
    $class_id   = $params['class_id'] ?? '';
    $status     = $params['status'] ?? '';
    $sdate      = $params['sdate'] ?? '';
    $edate      = $params['edate'] ?? '';

    $where = " WHERE 1=1 ";

    // 시험
    if ($mock_id !== '' && $mock_id !== null)
        $where .= " AND a.mock_id = '{$mock_id}' ";

    // 과목
    if ($subject_id !== '' && $subject_id !== null)
        $where .= " AND a.subject_id = '{$subject_id}' ";

    // 반
    if ($class_id !== '' && $class_id !== null)
        $where .= " AND m.class = '{$class_id}' ";

    // 응시여부
    if ($status !== '' && $status !== null)
        $where .= " AND a.status = '{$status}' ";

    // 기간 검색
    if ($sdate !== '')
        $where .= " AND a.applied_at >= '{$sdate} 00:00:00' ";

    if ($edate !== '')
        $where .= " AND a.applied_at <= '{$edate} 23:59:59' ";

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
        $where
        ORDER BY a.id DESC
        LIMIT $start, $rows
    ";
    // error_log(__FILE__.__LINE__."\n SQL: " . $sql);

    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;

    return $list;
}

/* ============================================================
   모의고사 응시현황 리스트 개수
============================================================ */
function select_mock_apply_listcnt($params = []) {

    $mock_id    = $params['mock_id'] ?? '';
    $subject_id = $params['subject_id'] ?? '';
    $class_id   = $params['class_id'] ?? '';
    $status     = $params['status'] ?? '';
    $sdate      = $params['sdate'] ?? '';
    $edate      = $params['edate'] ?? '';

    $where = " WHERE 1=1 ";

    if ($mock_id !== '' && $mock_id !== null)
        $where .= " AND a.mock_id = '{$mock_id}' ";

    if ($subject_id !== '' && $subject_id !== null)
        $where .= " AND a.subject_id = '{$subject_id}' ";

    if ($class_id !== '' && $class_id !== null)
        $where .= " AND m.class = '{$class_id}' ";

    if ($status !== '' && $status !== null)
        $where .= " AND a.status = '{$status}' ";

    if ($sdate !== '')
        $where .= " AND a.applied_at >= '{$sdate} 00:00:00' ";

    if ($edate !== '')
        $where .= " AND a.applied_at <= '{$edate} 23:59:59' ";

    $sql = "
        SELECT COUNT(*) AS cnt
        FROM cn_mock_apply AS a
        JOIN g5_member AS m ON a.mb_id = m.mb_id
        LEFT JOIN cn_class AS c ON m.class = c.id
        LEFT JOIN cn_mock_test AS mt ON a.mock_id = mt.id
        LEFT JOIN cn_mock_subject AS ms ON a.subject_id = ms.id
        $where
    ";

    $row = sql_fetch($sql);
    return (int)($row['cnt'] ?? 0);
}

/* ============================================================
   단건 조회
============================================================ */
function select_mock_apply_one($id) {
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
function insert_mock_apply($data = []) {

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
function update_mock_apply($id, $data = []) {
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
function delete_mock_apply($id) {
    $id = intval($id);
    $sql = "DELETE FROM cn_mock_apply WHERE id = '{$id}' ";
    return sql_query($sql);
}

?>
