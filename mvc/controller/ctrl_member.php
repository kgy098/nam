<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

// type
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
        1) 회원 목록 조회 (학생/교사)
        type = MEMBER_LIST
        파라미터: mode, page, rows, keyword, start_date, end_date 등
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
        type = MEMBER_GET
        파라미터: mb_id
    ------------------------------------------------------ */
    case 'MEMBER_GET':
        $mb_id = trim($req['mb_id'] ?? '');

        if ($mb_id === '') {
            $res['data'] = 'invalid mb_id';
            break;
        }

        $row = select_member_one_by_id($mb_id);

        $res = [
            'result' => 'SUCCESS',
            'data'   => $row
        ];
        break;


    /* ------------------------------------------------------
        3) 중복 체크
        type = MEMBER_CHECK_DUP
        파라미터: mb_name, mb_hp
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
        type = MEMBER_ADD
    ------------------------------------------------------ */
    case 'MEMBER_ADD':
        $ret = insert_member_full($req);

        if ($ret['ok']) {
            $res = [
                'result' => 'SUCCESS',
                'data'   => $ret['data']
            ];
        } else {
            $res['data'] = $ret['error'];
        }
        break;


    /* ------------------------------------------------------
        5) 회원 수정 (UPDATE)
        type = MEMBER_UPD
    ------------------------------------------------------ */
    case 'MEMBER_UPD':
        $ret = update_member_full($req);

        if ($ret['ok']) {
            $res = [
                'result' => 'SUCCESS',
                'data'   => $ret['data']
            ];
        } else {
            $res['data'] = $ret['error'];
        }
        break;


    /* ------------------------------------------------------
        6) 회원 삭제
        type = MEMBER_DEL
        파라미터: mb_id
    ------------------------------------------------------ */
    case 'MEMBER_DEL':
        $mb_id = trim($req['mb_id'] ?? '');

        if ($mb_id === '') {
            $res['data'] = 'invalid mb_id';
            break;
        }

        $ret = delete_member_by_id($mb_id);

        if ($ret['ok']) {
            $res = [
                'result' => 'SUCCESS',
                'data'   => $ret['data']
            ];
        } else {
            $res['data'] = $ret['error'];
        }
        break;


    /* ------------------------------------------------------
        기본: 잘못된 type
    ------------------------------------------------------ */
    default:
        $res['data'] = "Invalid type: {$type}";
        break;
}

echo json_encode($res, JSON_UNESCAPED_UNICODE);
exit;
