<?php

function select_qna_list($start=0, $num=CN_PAGE_NUM) {
    $sql = "select * from cn_qna
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_qna_listcnt() {
    $row = sql_fetch("select count(id) as cnt from cn_qna");
    return $row['cnt'];
}

function select_qna_one($id) {
    return sql_fetch("select * from cn_qna where id = {$id}");
}

function select_qna_by_student($student_mb_id, $status=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "student_mb_id = '{$student_mb_id}'";
    if (!is_null($status)) $where .= " and status = '{$status}'";
    $sql = "select * from cn_qna
            where {$where}
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function select_qna_by_teacher($teacher_mb_id, $status=null, $start=0, $num=CN_PAGE_NUM) {
    $where = "teacher_mb_id = '{$teacher_mb_id}'";
    if (!is_null($status)) $where .= " and status = '{$status}'";
    $sql = "select * from cn_qna
            where {$where}
            order by id desc
            limit $start, $num";
    $result = sql_query($sql);
    $list = [];
    while ($row = sql_fetch_array($result)) $list[] = $row;
    return $list;
}

function insert_qna_question($student_mb_id, $title, $question, $teacher_mb_id=null) {
    $teacher_sql = is_null($teacher_mb_id) ? "null" : "'{$teacher_mb_id}'";
    $sql = "insert into cn_qna
            set student_mb_id = '{$student_mb_id}',
                teacher_mb_id = {$teacher_sql},
                title = '{$title}',
                question = '{$question}',
                status = '미답변'";
    return sql_query($sql);
}

function answer_qna($id, $teacher_mb_id, $answer, $answered_dt) {
    $sql = "update cn_qna
            set teacher_mb_id = '{$teacher_mb_id}',
                answer = '{$answer}',
                status = '답변완료',
                answered_dt = '{$answered_dt}'
            where id = {$id}";
    return sql_query($sql);
}

function update_qna_question($id, $title, $question, $teacher_mb_id=null, $status=null) {
    $teacher_sql = is_null($teacher_mb_id) ? "null" : "'{$teacher_mb_id}'";
    $status_sql = is_null($status) ? "status" : "'{$status}'";
    $sql = "update cn_qna
            set title = '{$title}',
                question = '{$question}',
                teacher_mb_id = {$teacher_sql},
                status = {$status_sql}
            where id = {$id}";
    return sql_query($sql);
}

function delete_qna($id) {
    return sql_query("delete from cn_qna where id = {$id}");
}
?>
