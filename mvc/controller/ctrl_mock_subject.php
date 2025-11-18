<?php
include_once('./_common.php');
// CRUD 함수는 공통에서 이미 include 되고 있으므로 별도 include 불필요
// include_once('./cn_mock_subject_crud.php');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

switch($type){

/* ------------------------------------------------------
 * 리스트
 * ------------------------------------------------------ */
case 'MOCK_SUBJECT_LIST':

    $page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
    $rows = isset($_REQUEST['rows']) ? max(1, min(200, (int)$_REQUEST['rows'])) : 20;

    $start = ($page - 1) * $rows;

    // mock_id 삭제 → 관련 필터 없음
    // keyword 필터도 화면 요구가 없다면 제거 (필요하면 추후 추가)
    
    $list  = select_mock_subject_list($start, $rows);
    $total = select_mock_subject_listcnt();

    jres(true, [
        'total' => $total,
        'list'  => $list,
        'page'  => $page,
        'rows'  => $rows
    ]);
    break;


/* ------------------------------------------------------
 * 단건 조회
 * ------------------------------------------------------ */
case 'MOCK_SUBJECT_GET':

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = select_mock_subject_one($id);
    if (!$row) jres(false, 'not found');

    jres(true, $row);
    break;


/* ------------------------------------------------------
 * 등록
 * ------------------------------------------------------ */
case 'MOCK_SUBJECT_CREATE':

    $subject_name = trim($_REQUEST['subject_name'] ?? '');

    if ($subject_name === '') {
        jres(false, 'required');
    }

    // CRUD 호출
    $ok = insert_mock_subject($subject_name);
    if (!$ok) jres(false, 'insert fail');

    // 등록 직후 데이터 1건
    $new = select_mock_subject_list(0, 1);
    jres(true, $new[0]);
    break;


/* ------------------------------------------------------
 * 수정
 * ------------------------------------------------------ */
case 'MOCK_SUBJECT_UPDATE':

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $subject_name = isset($_REQUEST['subject_name'])
        ? trim($_REQUEST['subject_name'])
        : null;

    // CRUD 호출
    $ok = update_mock_subject($id, $subject_name);
    if (!$ok) jres(false, 'update fail');

    $row = select_mock_subject_one($id);
    jres(true, $row);
    break;


/* ------------------------------------------------------
 * 삭제 (soft delete)
 * ------------------------------------------------------ */
case 'MOCK_SUBJECT_DELETE':

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    // CRUD 호출
    $ok = soft_delete_mock_subject($id);
    if (!$ok) jres(false, 'delete fail');

    jres(true, 'deleted');
    break;


/* ------------------------------------------------------ */
default:
    jres(false, 'invalid type');
}
