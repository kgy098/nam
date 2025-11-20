<?php
include_once('./_common.php');

$type = $_POST['type'] ?? '';

switch ($type) {

  // 리스트
  case AJAX_MOCK_TEST_LIST:
    $list = select_mock_test_list($_POST);
    echo json_encode(['result' => 'SUCCESS', 'data' => $list]);
    break;

  // 단건 조회
  case AJAX_MOCK_TEST_GET:
    $id = $_POST['id'] ?? 0;
    $row = select_mock_test_one($id);
    echo json_encode(['result' => 'SUCCESS', 'data' => $row]);
    break;

  // 등록
  case AJAX_MOCK_TEST_CREATE:
    $new_id = insert_mock_test($_POST);
    echo json_encode(['result' => 'SUCCESS', 'id' => $new_id]);
    break;

  // 수정
  case AJAX_MOCK_TEST_UPDATE:
    $id = $_POST['id'] ?? 0;
    $payload = $_POST;
    unset($payload['type'], $payload['id']);

    update_mock_test($id, $payload);
    echo json_encode(['result' => 'SUCCESS']);
    break;

  // 삭제
  case AJAX_MOCK_TEST_DELETE:
    $id = $_POST['id'] ?? 0;
    delete_mock_test($id);
    echo json_encode(['result' => 'SUCCESS']);
    break;

  default:
    echo json_encode(['result' => 'FAIL', 'msg' => 'Unknown type']);
    break;
}

exit;
