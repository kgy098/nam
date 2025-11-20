<?php
include_once('./_common.php');

$type = $_POST['type'] ?? '';

switch ($type) {

    case AJAX_MOCK_APPLY_LIST:
        $list = select_mock_apply_list($_POST);
        echo json_encode(['result'=>'SUCCESS','data'=>$list]);
        break;

    case AJAX_MOCK_APPLY_GET:
        $row = select_mock_apply_one($_POST['id']);
        echo json_encode(['result'=>'SUCCESS','data'=>$row]);
        break;

    case AJAX_MOCK_APPLY_CREATE:
        $id = insert_mock_apply($_POST);
        echo json_encode(['result'=>'SUCCESS','id'=>$id]);
        break;

    case AJAX_MOCK_APPLY_UPDATE:
        $id = $_POST['id'];
        $payload = $_POST;
        unset($payload['type'], $payload['id']);
        update_mock_apply($id, $payload);
        echo json_encode(['result'=>'SUCCESS']);
        break;

    case AJAX_MOCK_APPLY_DELETE:
        delete_mock_apply($_POST['id']);
        echo json_encode(['result'=>'SUCCESS']);
        break;

    default:
        echo json_encode(['result'=>'FAIL','msg'=>'Unknown type']);
        break;
}

exit;
?>
