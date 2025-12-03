<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

/**********************************************************************
 * Q&A 컨트롤러
 * 규칙:
 * - if / else if 구조 사용
 * - Esc() 사용 안함
 * - CRUD 함수 호출 방식
 * - cn_qna 스키마 100% 일치
 * - type 상수 없이 문자 그대로 사용(너의 규칙)
 **********************************************************************/

$type = $_REQUEST['type'] ?? '';

/* QNA_LIST
 * 파라미터:
 *  - page
 *  - rows
 *  - student_mb_id (선택)
 *  - teacher_mb_id (선택)
 *  - status (선택)
 *  - keyword (선택) → title, question 검색
 */
if ($type === 'QNA_LIST') {

    $page = max(1, (int)($_REQUEST['page'] ?? 1));
    $rows = max(1, min(200, (int)($_REQUEST['rows'] ?? 20)));
    $offset = ($page - 1) * $rows;

    $student_mb_id = trim($_REQUEST['student_mb_id'] ?? '');
    $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
    $status        = trim($_REQUEST['status'] ?? '');
    $keyword       = trim($_REQUEST['keyword'] ?? '');

    // WHERE 조합
    $where = "1";

    if ($student_mb_id !== '') {
        $where .= " AND student_mb_id = '{$student_mb_id}'";
    }

    if ($teacher_mb_id !== '') {
        $where .= " AND teacher_mb_id = '{$teacher_mb_id}'";
    }

    if ($status !== '') {
        $where .= " AND status = '{$status}'";
    }

    if ($keyword !== '') {
        $k = $keyword;
        $where .= " AND (title LIKE '%{$k}%' OR question LIKE '%{$k}%')";
    }

    // 전체 개수
    $total = (int)sql_fetch("SELECT COUNT(*) cnt FROM cn_qna WHERE {$where}")['cnt'];

    // 리스트
    $list = [];
    $sql = "
        SELECT id, student_mb_id, teacher_mb_id,
               title, question, answer, status, answered_dt,
               reg_dt, mod_dt
        FROM cn_qna
        WHERE {$where}
        ORDER BY id DESC
        LIMIT {$offset}, {$rows}
    ";
    $q = sql_query($sql);
    while ($row = sql_fetch_array($q)) $list[] = $row;

    jres(true, [
        'total' => $total,
        'list'  => $list,
        'page'  => $page,
        'rows'  => $rows
    ]);
}


/* QNA_GET
 * 단건 조회
 */
else if ($type === 'QNA_GET') {

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = select_qna_one($id);
    if (!$row) jres(false, 'not found');

    jres(true, $row);
}


/* QNA_CREATE
 * 질문 등록
 * 파라미터:
 *  - student_mb_id
 *  - title
 *  - question
 *  - teacher_mb_id (optional)
 */
else if ($type === 'QNA_CREATE') {

    $student_mb_id = trim($_REQUEST['student_mb_id'] ?? '');
    $title         = trim($_REQUEST['title'] ?? '');
    $question      = trim($_REQUEST['question'] ?? '');
    $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');

    if ($student_mb_id === '' || $title === '' || $question === '') {
        jres(false, 'required');
    }

    $teacher_val = ($teacher_mb_id === '') ? null : $teacher_mb_id;

    $ok = insert_qna_question(
        $student_mb_id,
        $title,
        $question,
        $teacher_val
    );

    if (!$ok) jres(false, 'insert fail');

    // 마지막 insert row 반환
    $new = sql_fetch("SELECT * FROM cn_qna ORDER BY id DESC LIMIT 1");
    jres(true, $new);
}


/* QNA_UPDATE
 * 질문 수정
 * 파라미터:
 *  - id
 *  - title
 *  - question
 *  - teacher_mb_id (optional)
 *  - status (optional)
 */
else if ($type === 'QNA_UPDATE') {

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $title         = trim($_REQUEST['title'] ?? '');
    $question      = trim($_REQUEST['question'] ?? '');
    $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
    $status        = trim($_REQUEST['status'] ?? '');

    $teacher_val = ($teacher_mb_id === '') ? null : $teacher_mb_id;
    $status_val  = ($status === '') ? null : $status;

    $ok = update_qna_question(
        $id,
        $title,
        $question,
        $teacher_val,
        $status_val
    );

    if (!$ok) jres(false, 'update fail');

    $row = select_qna_one($id);
    jres(true, $row);
}


/* QNA_ANSWER
 * 선생님 답변 등록
 * 파라미터:
 *  - id
 *  - teacher_mb_id
 *  - answer
 *  - answered_dt (optional, 없으면 NOW)
 */
else if ($type === 'QNA_ANSWER') {

    $id            = (int)($_REQUEST['id'] ?? 0);
    $teacher_mb_id = trim($_REQUEST['teacher_mb_id'] ?? '');
    $answer        = trim($_REQUEST['answer'] ?? '');
    $answered_dt   = trim($_REQUEST['answered_dt'] ?? '');

    if ($id <= 0 || $teacher_mb_id === '' || $answer === '') {
        jres(false, 'required');
    }

    // answered_dt 처리
    // (빈 문자열이면 NULL)
    $answered_val = ($answered_dt === '') ? null : $answered_dt;

    $ok = answer_qna(
        $id,
        $teacher_mb_id,
        $answer,
        $answered_val
    );

    if (!$ok) jres(false, 'answer fail');

    $row = select_qna_one($id);
    jres(true, $row);
}


/* QNA_DELETE */
else if ($type === 'QNA_DELETE') {

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $ok = delete_qna($id);
    if (!$ok) jres(false, 'delete fail');

    jres(true, 'deleted');
}


/* invalid type */
else {
    jres(false, 'invalid type');
}

?>
