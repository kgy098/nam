<?php
include_once('./_common.php');
header('Content-Type: application/json; charset=utf-8');

$type = isset($_REQUEST['type']) ? $_REQUEST['type'] : '';
$table = 'cn_consult';
$pk = 'id';

function jres($ok, $data = null) {
    echo json_encode(['result' => $ok ? 'SUCCESS' : 'FAIL', 'data' => $data], JSON_UNESCAPED_UNICODE);
    exit;
}
function esc($s) { if (function_exists('sql_escape_string')) return sql_escape_string($s); return addslashes($s); }

switch ($type) {
    case 'CONSULT_LIST':
        $page = isset($_REQUEST['page']) ? max(1, (int)$_REQUEST['page']) : 1;
        $rows = isset($_REQUEST['rows']) ? max(1, min(200, (int)$_REQUEST['rows'])) : 20;
        $offset = ($page - 1) * $rows;
        $keyword = isset($_REQUEST['keyword']) ? trim($_REQUEST['keyword']) : '';
        $status = isset($_REQUEST['status']) ? trim($_REQUEST['status']) : '';
        $student_id = isset($_REQUEST['student_mb_id']) ? trim($_REQUEST['student_mb_id']) : '';
        $teacher_id = isset($_REQUEST['teacher_mb_id']) ? trim($_REQUEST['teacher_mb_id']) : '';

        $where = '1';
        if ($keyword !== '') {
            $k = esc($keyword);
            $where .= " AND (title LIKE '%{$k}%' OR content LIKE '%{$k}%')";
        }
        if ($status !== '') $where .= " AND status='" . esc($status) . "'";
        if ($student_id !== '') $where .= " AND student_mb_id='" . esc($student_id) . "'";
        if ($teacher_id !== '') $where .= " AND teacher_mb_id='" . esc($teacher_id) . "'";

        $cnt_row = sql_fetch("SELECT COUNT(*) AS cnt FROM {$table} WHERE {$where}");
        $total = (int)$cnt_row['cnt'];

        $list = [];
        $q = sql_query("SELECT {$pk}, student_mb_id, teacher_mb_id, title, content, status, scheduled_at, created_at, updated_at
                        FROM {$table}
                        WHERE {$where}
                        ORDER BY {$pk} DESC
                        LIMIT {$offset}, {$rows}");
        for ($i = 0; $row = sql_fetch_array($q); $i++) $list[] = $row;

        jres(true, ['total' => $total, 'list' => $list, 'page' => $page, 'rows' => $rows]);
        break;

    case 'CONSULT_GET':
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($id <= 0) jres(false, 'invalid id');
        $row = sql_fetch("SELECT {$pk}, student_mb_id, teacher_mb_id, title, content, status, scheduled_at, created_at, updated_at
                          FROM {$table} WHERE {$pk}={$id}");
        if (!$row) jres(false, 'not found');
        jres(true, $row);
        break;

    case 'CONSULT_CREATE':
        $student_mb_id = isset($_REQUEST['student_mb_id']) ? esc(trim($_REQUEST['student_mb_id'])) : '';
        $teacher_mb_id = isset($_REQUEST['teacher_mb_id']) ? esc(trim($_REQUEST['teacher_mb_id'])) : '';
        $title = isset($_REQUEST['title']) ? esc(trim($_REQUEST['title'])) : '';
        $content = isset($_REQUEST['content']) ? esc(trim($_REQUEST['content'])) : '';
        $status = isset($_REQUEST['status']) ? esc(trim($_REQUEST['status'])) : 'REQUESTED';
        $scheduled_at = isset($_REQUEST['scheduled_at']) && $_REQUEST['scheduled_at'] !== '' ? "'" . esc($_REQUEST['scheduled_at']) . "'" : 'NULL';

        if ($student_mb_id === '' || $title === '') jres(false, 'required');

        $sql = "INSERT INTO {$table}
                (student_mb_id, teacher_mb_id, title, content, status, scheduled_at, created_at, updated_at)
                VALUES ('{$student_mb_id}', '{$teacher_mb_id}', '{$title}', '{$content}', '{$status}', {$scheduled_at}, NOW(), NOW())";
        $ok = sql_query($sql, false);
        if (!$ok) jres(false, 'insert fail');

        $new = sql_fetch("SELECT {$pk}, student_mb_id, teacher_mb_id, title, content, status, scheduled_at, created_at, updated_at
                          FROM {$table} ORDER BY {$pk} DESC LIMIT 1");
        jres(true, $new);
        break;

    case 'CONSULT_UPDATE':
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($id <= 0) jres(false, 'invalid id');

        $sets = [];
        if (isset($_REQUEST['student_mb_id'])) $sets[] = "student_mb_id='" . esc(trim($_REQUEST['student_mb_id'])) . "'";
        if (isset($_REQUEST['teacher_mb_id'])) $sets[] = "teacher_mb_id='" . esc(trim($_REQUEST['teacher_mb_id'])) . "'";
        if (isset($_REQUEST['title'])) $sets[] = "title='" . esc(trim($_REQUEST['title'])) . "'";
        if (isset($_REQUEST['content'])) $sets[] = "content='" . esc(trim($_REQUEST['content'])) . "'";
        if (isset($_REQUEST['status'])) $sets[] = "status='" . esc(trim($_REQUEST['status'])) . "'";
        if (array_key_exists('scheduled_at', $_REQUEST)) {
            $sets[] = "scheduled_at=" . ($_REQUEST['scheduled_at'] === '' ? "NULL" : ("'" . esc($_REQUEST['scheduled_at']) . "'"));
        }
        $sets[] = "updated_at=NOW()";

        $set_sql = implode(',', $sets);
        $ok = sql_query("UPDATE {$table} SET {$set_sql} WHERE {$pk}={$id}", false);
        if (!$ok) jres(false, 'update fail');

        $row = sql_fetch("SELECT {$pk}, student_mb_id, teacher_mb_id, title, content, status, scheduled_at, created_at, updated_at
                          FROM {$table} WHERE {$pk}={$id}");
        jres(true, $row);
        break;

    case 'CONSULT_DELETE':
        $id = isset($_REQUEST['id']) ? (int)$_REQUEST['id'] : 0;
        if ($id <= 0) jres(false, 'invalid id');
        $ok = sql_query("DELETE FROM {$table} WHERE {$pk}={$id}", false);
        if (!$ok) jres(false, 'delete fail');
        jres(true, 'deleted');
        break;

    default:
        jres(false, 'invalid type');
}
