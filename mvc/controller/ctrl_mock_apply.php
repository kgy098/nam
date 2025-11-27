<?php
include_once('./_common.php');

$type = $_POST['type'] ?? '';

switch ($type) {

  case AJAX_MOCK_APPLY_LIST:
    $list = select_mock_apply_list($_POST);
    echo json_encode(['result' => 'SUCCESS', 'data' => $list]);
    break;

  case AJAX_MOCK_APPLY_GET:
    $row = select_mock_apply_one($_POST['id']);
    echo json_encode(['result' => 'SUCCESS', 'data' => $row]);
    break;

  case AJAX_MOCK_APPLY_CREATE:
    $id = insert_mock_apply($_POST);
    echo json_encode(['result' => 'SUCCESS', 'id' => $id]);
    break;

  case AJAX_MOCK_APPLY_UPDATE:
    $id = $_POST['id'];
    $payload = $_POST;
    unset($payload['type'], $payload['id']);
    update_mock_apply($id, $payload);
    echo json_encode(['result' => 'SUCCESS']);
    break;

  case AJAX_MOCK_APPLY_DELETE:
    delete_mock_apply($_POST['id']);
    echo json_encode(['result' => 'SUCCESS']);
    break;
  case 'MOCK_APPLY_MY_LIST':
    $mb_id = $_SESSION['ss_mb_id'];
    $list = select_mock_apply_my_list($mb_id);
    echo json_encode(['result' => 'SUCCESS', 'data' => $list]);
    break;

  case 'MOCK_APPLY_MY_STATUS':
    $mock_id = $_POST['mock_id'];
    $mb_id   = $_SESSION['ss_mb_id'];
    $map = select_mock_apply_my_status($mock_id, $mb_id);
    echo json_encode(['result' => 'SUCCESS', 'data' => $map]);
    break;

  case 'MOCK_APPLY_TOGGLE':
    $mock_id    = $_POST['mock_id'];
    $subject_id = $_POST['subject_id'];
    $mb_id      = $_SESSION['ss_mb_id'];

    $res = toggle_mock_apply($mock_id, $subject_id, $mb_id);
    echo json_encode($res);
    break;

  case 'MOCK_APPLY_MY_OVERVIEW_LIST':

    $page = $_POST['page'] ?? 1;
    $rows = $_POST['rows'] ?? 5;
    $start = ($page - 1) * $rows;

    $mb_id = $member['mb_id'];

    $list  = select_mock_apply_my_overview_list($mb_id, $start, $rows);
    $total = select_mock_apply_my_overview_listcnt($mb_id);

    echo json_encode([
        'result' => 'SUCCESS',
        'data' => [
            'list'  => $list,
            'total' => $total,
            'page'  => $page,
            'rows'  => $rows
        ]
    ]);
    exit;


  default:
    echo json_encode(['result' => 'FAIL', 'msg' => 'Unknown type']);
    break;
}

exit;
