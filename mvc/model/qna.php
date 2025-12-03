<?php

/* Q&A 목록 */
function select_qna_list($start = 0, $num = CN_PAGE_NUM)
{
  $start = (int)$start;
  $num   = (int)$num;

  $sql = "
        select *
        from cn_qna
        order by id desc
        limit {$start}, {$num}
    ";
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

/* Q&A 전체 개수 */
function select_qna_listcnt()
{
  $row = sql_fetch("select count(id) as cnt from cn_qna");
  return $row['cnt'];
}

/* 단건 조회 */
function select_qna_one($id)
{
  $id = (int)$id;
  return sql_fetch("select * from cn_qna where id = {$id}");
}

/* 학생별 조회 */
function select_qna_by_student($student_mb_id, $status = null, $start = 0, $num = CN_PAGE_NUM)
{
  $start = (int)$start;
  $num   = (int)$num;

  $where = "student_mb_id = '{$student_mb_id}'";

  // status가 null 또는 "" 이 아닐 때만 조건 추가
  if (!is_null($status) && $status !== '') {
    $where .= " and status = '{$status}'";
  }

  $sql = "
        select *
        from cn_qna
        where {$where}
        order by id desc
        limit {$start}, {$num}
    ";
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

/* 선생님별 조회 */
function select_qna_by_teacher($teacher_mb_id, $status = null, $start = 0, $num = CN_PAGE_NUM)
{
  $start = (int)$start;
  $num   = (int)$num;

  $where = "teacher_mb_id = '{$teacher_mb_id}'";

  if (!is_null($status) && $status !== '') {
    $where .= " and status = '{$status}'";
  }

  $sql = "
        select *
        from cn_qna
        where {$where}
        order by id desc
        limit {$start}, {$num}
    ";
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

/* 질문 등록 */
function insert_qna_question($student_mb_id, $title, $question, $teacher_mb_id = null)
{

  // teacher_mb_id null 처리
  $teacher_sql = is_null($teacher_mb_id) ? "NULL" : "'{$teacher_mb_id}'";

  $sql = "
        insert into cn_qna
        set student_mb_id = '{$student_mb_id}',
            teacher_mb_id = {$teacher_sql},
            title = '{$title}',
            question = '{$question}',
            status = '미답변'
    ";
  return sql_query($sql);
}

/* 답변 등록 */
function answer_qna($id, $teacher_mb_id, $answer, $answered_dt)
{
  $id = (int)$id;
  $answered_sql = ($answered_dt === '' || is_null($answered_dt)) ? "NULL" : "'{$answered_dt}'";

  $sql = "
    update cn_qna
    set teacher_mb_id = '{$teacher_mb_id}',
        answer = '{$answer}',
        status = '답변완료',
        answered_dt = {$answered_sql}
    where id = {$id}
    ";
  return sql_query($sql);
}

/* 질문 수정 */
function update_qna_question($id, $title, $question, $teacher_mb_id = null, $status = null)
{
  $id = (int)$id;

  $teacher_sql = is_null($teacher_mb_id) ? "NULL" : "'{$teacher_mb_id}'";

  // 동적 SET 조합
  $set = [];
  $set[] = "title = '{$title}'";
  $set[] = "question = '{$question}'";
  $set[] = "teacher_mb_id = {$teacher_sql}";

  // status가 null 아니면 업데이트
  if (!is_null($status) && $status !== '') {
    $set[] = "status = '{$status}'";
  }

  $set_str = implode(", ", $set);

  $sql = "
        update cn_qna
        set {$set_str}
        where id = {$id}
    ";
  return sql_query($sql);
}

/* 삭제 */
function delete_qna($id)
{
  $id = (int)$id;
  return sql_query("delete from cn_qna where id = {$id}");
}
