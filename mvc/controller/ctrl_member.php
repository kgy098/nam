<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';

switch($type){

case 'MEMBER_LIST':
    $data = select_member_list_search($_REQUEST);
    jres(true, $data);
break;

case 'STUDENT_LIST':
    $data = select_member_list_search($_REQUEST);
    jres(true, $data);
break;

case 'TEACHER_LIST':
    $data = select_member_list_search($_REQUEST);
    jres(true, $data);
break;

case 'MEMBER_CHECK_DUP':
    $mb_name = esc($_POST['mb_name']);
    $mb_hp   = esc($_POST['mb_hp']);

    $exists = select_member_dup($mb_name, $mb_hp);
    jres(true, ['duplicate' => $exists]);
break;

case 'MEMBER_GET':
    $id = isset($_REQUEST['mb_id']) ? trim($_REQUEST['mb_id']) : '';
    if ($id==='') jres(false, 'invalid mb_id');
    $row = select_member_one_by_id($id);
    if (!$row) jres(false, 'not found');
    jres(true, $row);
break;

case 'MEMBER_CREATE':
    $res = insert_member_full($_REQUEST);
    if (!$res['ok']) jres(false, $res['error']);
    jres(true, $res['data']);
break;

case 'MEMBER_UPDATE':
    $res = update_member_full($_REQUEST);
    if (!$res['ok']) jres(false, $res['error']);
    jres(true, $res['data']);
break;

case 'MEMBER_DELETE':
    $id = isset($_REQUEST['mb_id']) ? trim($_REQUEST['mb_id']) : '';
    $res = delete_member_by_id($id);
    if (!$res['ok']) jres(false, $res['error']);
    jres(true, $res['data']);
break;

default:
    jres(false,'invalid type');
}
