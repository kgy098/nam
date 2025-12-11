<?php

/* Q&A 목록 */
function select_qna_list($student_mb_id, $teacher_mb_id, $status, $keyword, $start = 0, $num = CN_PAGE_NUM)
{
  $where = "1";

  if ($student_mb_id !== '') {
    $where .= " AND student_mb_id = '{$student_mb_id}'";
  }

  if ($teacher_mb_id !== '') {
    $where .= " AND teacher_mb_id = '{$teacher_mb_id}'";
  }

  if ($status !== '') {
    $where .= " AND status = '{$status}'";
  }

  if ($keyword !== '') {
    $k = $keyword;
    $where .= " AND (title LIKE '%{$k}%' OR question LIKE '%{$k}%' OR answer LIKE '%{$k}%' OR ms.mb_name LIKE '%{$k}%')";
  }

  $sql = "
        SELECT q.*, mt.mb_name as teacher_name, ms.mb_name as student_name, c.name as class_name
        FROM cn_qna q
          LEFT OUTER JOIN g5_member mt ON q.teacher_mb_id=mt.mb_id
          LEFT OUTER JOIN g5_member ms ON q.student_mb_id=ms.mb_id
          LEFT OUTER JOIN cn_class c ON ms.class=c.id
        WHERE {$where}
        ORDER BY id DESC
        LIMIT {$start}, {$num}
    ";
  elog($sql);
  $result = sql_query($sql);

  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

/* Q&A 전체 개수 */
function select_qna_listcnt($student_mb_id, $teacher_mb_id, $status, $keyword)
{
  $where = "1";

  if ($student_mb_id !== '') {
    $where .= " AND student_mb_id = '{$student_mb_id}'";
  }

  if ($teacher_mb_id !== '') {
    $where .= " AND teacher_mb_id = '{$teacher_mb_id}'";
  }

  if ($status !== '') {
    $where .= " AND status = '{$status}'";
  }

  if ($keyword !== '') {
    $k = $keyword;
    $where .= " AND (title LIKE '%{$k}%' OR question LIKE '%{$k}%' OR answer LIKE '%{$k}%' OR ms.mb_name LIKE '%{$k}%')";
  }

  $sql = "
        SELECT count(id) as cnt
        FROM cn_qna q
          LEFT OUTER JOIN g5_member mt ON q.teacher_mb_id=mt.mb_id
          LEFT OUTER JOIN g5_member ms ON q.student_mb_id=ms.mb_id
        WHERE {$where}
    ";

  $row = sql_fetch($sql);
  return $row['cnt'];
}

/* 단건 조회 */
function select_qna_one($id)
{
  $id = (int)$id;
  $sql = "
      SELECT q.*, mt.mb_name as teacher_name
      FROM cn_qna q
        LEFT JOIN g5_member mt ON q.teacher_mb_id=mt.mb_id
      WHERE id = {$id}
  ";

  return sql_fetch($sql);
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
