<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type = $_REQUEST['type'] ?? '';
switch($type){

/*******************************************************
 * VIDEO_LIST
 *******************************************************/
case 'VIDEO_LIST':

    $page = max(1, (int)($_REQUEST['page'] ?? 1));
    $num = max(1, min(200, (int)($_REQUEST['num'] ?? 20)));
    $offset = ($page - 1) * $num;

    $keyword = trim($_REQUEST['keyword'] ?? '');
    $class   = trim($_REQUEST['class_name'] ?? '');
    $mb_id   = trim($_REQUEST['mb_id'] ?? '');

    // 기본 리스트 전체에서 필터링
    $all = select_video_list(0, 999999);

    // 필터 처리
    $filtered = [];
    foreach($all as $v){
        if ($keyword !== '') {
            if (strpos($v['title'], $keyword) === false &&
                strpos($v['description'], $keyword) === false){
                continue;
            }
        }
        if ($class !== '' && $v['class_name'] !== $class) continue;
        if ($mb_id !== '' && $v['mb_id'] !== $mb_id) continue;

        $filtered[] = $v;
    }

    $total = count($filtered);

    // 페이지네이션
    $list = array_slice($filtered, $offset, $num);
    // error_log(__FILE__.__LINE__."\nData: " . $list);

    jres(true, [
        'total' => $total,
        'list'  => $list,
        'page'  => $page,
        'num'  => $num
    ]);
    break;



/*******************************************************
 * VIDEO_GET
 *******************************************************/
case 'VIDEO_GET':

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = select_video_one($id);
    if (!$row) jres(false, 'not found');

    jres(true, $row);
    break;



/*******************************************************
 * VIDEO_CREATE
 *******************************************************/
case 'VIDEO_CREATE':

    $title       = trim($_REQUEST['title'] ?? '');
    $youtube_id  = trim($_REQUEST['youtube_id'] ?? '');
    $description = trim($_REQUEST['description'] ?? '');
    $class_name  = trim($_REQUEST['class_name'] ?? '');
    $mb_id       = trim($_REQUEST['mb_id'] ?? '');

    if ($title === '' || $youtube_id === '') {
        jres(false, 'required');
    }

    $ok = insert_video($title, $youtube_id, $description, $class_name, $mb_id);
    if (!$ok) jres(false, 'insert fail');

    // 방금 생성된 가장 마지막 ID
    $list = select_video_list(0,1);
    $new = $list[0] ?? null;

    jres(true, $new);
    break;



/*******************************************************
 * VIDEO_UPDATE
 *******************************************************/
case 'VIDEO_UPDATE':

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = select_video_one($id);
    if (!$row) jres(false, 'not found');

    $title       = trim($_REQUEST['title'] ?? $row['title']);
    $youtube_id  = trim($_REQUEST['youtube_id'] ?? $row['youtube_id']);
    $description = trim($_REQUEST['description'] ?? $row['description']);
    $class_name  = trim($_REQUEST['class_name'] ?? $row['class_name']);

    $ok = update_video($id, $title, $youtube_id, $description, $class_name);
    if (!$ok) jres(false, 'update fail');

    jres(true, select_video_one($id));
    break;



/*******************************************************
 * VIDEO_DELETE
 *******************************************************/
case 'VIDEO_DELETE':

    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) jres(false, 'invalid id');

    $row = select_video_one($id);
    if (!$row) jres(false, 'not found');

    $ok = delete_video($id);
    if (!$ok) jres(false, 'delete fail');

    jres(true, 'deleted');
    break;



default:
    jres(false, 'invalid type');
}
