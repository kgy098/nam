<?php
include_once('./_common.php');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

switch ($type) {


  /* ------------------------------------------------------
 * 리스트
 * ------------------------------------------------------ */
  case 'MOCK_SUBJECT_LIST':

    $page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
    $num  = isset($_REQUEST['num']) ? max(1, min(200, (int)$_REQUEST['num'])) : 20;

    $start = ($page - 1) * $num;

    /* ★ type 은 필수값 */
    $subject_type = $_REQUEST['subject_type'] ?? '';

    $list  = select_mock_subject_list($start, $num, $subject_type);
    $total = select_mock_subject_listcnt($subject_type);

    jres(true, [
      'total' => $total,
      'list'  => $list,
      'page'  => $page,
      'num'   => $num
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
    $subject_type = trim($_REQUEST['subject_type'] ?? '');

    if ($subject_name === '' || $subject_type === '') {
      jres(false, 'required');
    }

    $ok = insert_mock_subject($subject_name, $subject_type);
    if (!$ok) jres(false, 'insert fail');

    // 등록 후 type 기준 가장 최신 1건 조회
    $new = select_mock_subject_list(0, 1, $subject_type);
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

    /* ★ type 수정도 반영 가능하도록 확장 */
    $subject_type = isset($_REQUEST['subject_type'])
      ? trim($_REQUEST['subject_type'])
      : null;

    $ok = update_mock_subject($id, $subject_name, $subject_type);
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

    $ok = soft_delete_mock_subject($id);
    if (!$ok) jres(false, 'delete fail');

    jres(true, 'deleted');
    break;



  /* ------------------------------------------------------ */
  default:
    jres(false, 'invalid type');
}
