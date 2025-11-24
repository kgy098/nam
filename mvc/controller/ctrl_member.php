<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type = $_REQUEST['type'] ?? '';
$req  = $_REQUEST;

$res = [
    'result' => 'FAIL',
    'data'   => null
];

/* ==========================================================
    SWITCH
========================================================== */
switch ($type) {

    /* ------------------------------------------------------
        1) 회원 목록 조회
    ------------------------------------------------------ */
    case 'MEMBER_LIST':

        $list = select_member_list_search($req);

        $res = [
            'result' => 'SUCCESS',
            'data'   => [
                'total' => $list['total'],
                'list'  => $list['list'],
                'page'  => $list['page'],
                'rows'  => $list['rows']
            ]
        ];
        break;

    /* ------------------------------------------------------
        2) 단건 조회
        (mb_id 기준)
    ------------------------------------------------------ */
    case 'MEMBER_GET':

        $mb_id = trim($req['mb_id'] ?? '');

        if ($mb_id === '') {
            $res['data'] = 'invalid mb_id';
            break;
        }

        $row = select_member_one($mb_id);

        if (!$row) {
            $res['data'] = 'not_found';
            break;
        }

        $res = [
            'result' => 'SUCCESS',
            'data'   => $row
        ];
        break;

    /* ------------------------------------------------------
        3) 중복 체크
    ------------------------------------------------------ */
    case 'MEMBER_CHECK_DUP':

        $mb_name = trim($req['mb_name'] ?? '');
        $mb_hp   = trim($req['mb_hp'] ?? '');

        $dup = select_member_dup($mb_name, $mb_hp);

        $res = [
            'result' => 'SUCCESS',
            'data'   => ['duplicate' => $dup]
        ];
        break;

    /* ------------------------------------------------------
        4) 회원 등록 (INSERT)
    ------------------------------------------------------ */
    case 'MEMBER_ADD':

        $row = insert_member_full($req);

        if ($row) {
            $res = [
                'result' => 'SUCCESS',
                'data'   => $row
            ];
        } else {
            $res['data'] = 'insert_fail';
        }
        break;

    /* ------------------------------------------------------
        5) 회원 수정 (UPDATE)
    ------------------------------------------------------ */
    case 'MEMBER_UPD':

        $row = update_member_full($req);

        if ($row) {
            $res = [
                'result' => 'SUCCESS',
                'data'   => $row
            ];
        } else {
            $res['data'] = 'update_fail';
        }
        break;

    /* ------------------------------------------------------
        6) 회원 삭제 (DELETE)
    ------------------------------------------------------ */
    case 'MEMBER_DEL':

        $mb_id = trim($req['mb_id'] ?? '');

        if ($mb_id === '') {
            $res['data'] = 'invalid mb_id';
            break;
        }

        $ok = delete_member_by_id($mb_id);

        if ($ok) {
            $res = [
                'result' => 'SUCCESS',
                'data'   => 'deleted'
            ];
        } else {
            $res['data'] = 'delete_fail';
        }
        break;

    /* ------------------------------------------------------
        7) 잘못된 TYPE
    ------------------------------------------------------ */
    default:
        $res['data'] = "Invalid type: {$type}";
        break;
}

echo json_encode($res, JSON_UNESCAPED_UNICODE);
exit;
