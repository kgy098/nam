<?php

function select_consult_list($start = 0, $num = CN_PAGE_NUM)
{
  $sql = "select * from cn_consult
            order by requested_dt desc, id desc
            limit $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_consult_listcnt()
{
  $row = sql_fetch("select count(id) as cnt from cn_consult");
  return $row['cnt'];
}

function select_consult_one($id)
{
  return sql_fetch("select * from cn_consult where id = {$id}");
}

function select_consult_by_student($student_mb_id, $type, $start = 0, $num = CN_PAGE_NUM)
{
  $sql = "select * from cn_consult
            where student_mb_id = '{$student_mb_id}' and type = '{$type}'
            order by requested_dt desc, id desc
            limit $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_consult_by_teacher($teacher_mb_id, $type, $status = null, $start = 0, $num = CN_PAGE_NUM)
{
  $where = "teacher_mb_id = '{$teacher_mb_id}' and type = '{$type}'";
  if (!is_null($status)) $where .= " and status = '{$status}'";

  $sql = "select * from cn_consult
            where {$where}
            order by requested_dt desc, id desc
            limit $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_consult_by_teacher_and_date($teacher_mb_id, $type, $target_date)
{
  $type_sql = "";
  if ( !empty($type) ) {
    $type_sql = " AND type = '{$type}' ";
  }
  $sql = "select c.*, m.mb_name, cl.name as class_name
            from cn_consult c
              LEFT OUTER JOIN g5_member m on m.mb_id=student_mb_id
              LEFT OUTER JOIN cn_class cl on m.class = cl.id
            where teacher_mb_id = '{$teacher_mb_id}'
              $type_sql
              and date(scheduled_dt) = '{$target_date}'
            order by scheduled_dt asc, id asc";
  elog($sql);
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_consult_by_teacher_and_datetime($teacher_mb_id, $type, $scheduled_dt)
{
  $sql = "select *
            from cn_consult
            where teacher_mb_id = '{$teacher_mb_id}'
              and type = '{$type}'
              and scheduled_dt = '{$scheduled_dt}'
            limit 1";
  return sql_fetch($sql);
}

// 앱 선생님 화면용
function select_consult_list_by_teacher($teacher_mb_id, $consult_type, $target_date)
{
  $teacher_mb_id = sql_escape_string($teacher_mb_id);
  $consult_type  = sql_escape_string($consult_type);
  $target_date   = sql_escape_string($target_date);

  $sql = "
        SELECT 
            c.*,
            m.mb_name
        FROM cn_consult AS c
        LEFT JOIN g5_member AS m
            ON m.mb_id = c.student_mb_id
        WHERE c.teacher_mb_id = '{$teacher_mb_id}'
          AND c.type = '{$consult_type}'
          AND DATE(c.scheduled_dt) = '{$target_date}'
        ORDER BY c.scheduled_dt ASC
    ";
  // elog("SQL: $sql");
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}


function insert_consult_slot($student_mb_id, $teacher_mb_id, $type, $scheduled_dt, $memo = null)
{
  $memo_sql = is_null($memo) ? "null" : "'{$memo}'";
  $sql = "insert into cn_consult
            set student_mb_id = '{$student_mb_id}',
                teacher_mb_id = '{$teacher_mb_id}',
                type = '{$type}',
                requested_dt = now(),
                scheduled_dt = '{$scheduled_dt}',
                status = '예약완료',
                memo = {$memo_sql}";
  elog("$sql");
  return sql_query($sql);
}

function insert_consult($student_mb_id, $teacher_mb_id, $type, $requested_dt, $scheduled_dt = null, $status = '예약요청', $memo = null)
{
  $scheduled = is_null($scheduled_dt) ? "null" : "'{$scheduled_dt}'";
  $memo_sql = is_null($memo) ? "null" : "'{$memo}'";
  $sql = "insert into cn_consult
            set student_mb_id = '{$student_mb_id}',
                teacher_mb_id = '{$teacher_mb_id}',
                type = '{$type}',
                requested_dt = '{$requested_dt}',
                scheduled_dt = {$scheduled},
                status = '{$status}',
                memo = {$memo_sql}";
  return sql_query($sql);
}

function update_consult($id, $teacher_mb_id, $type, $scheduled_dt = null, $status = '예약요청', $memo = null)
{
  $scheduled = is_null($scheduled_dt) ? "null" : "'{$scheduled_dt}'";
  $memo_sql = is_null($memo) ? "null" : "'{$memo}'";
  $sql = "update cn_consult
            set teacher_mb_id = '{$teacher_mb_id}',
                type = '{$type}',
                scheduled_dt = {$scheduled},
                status = '{$status}',
                memo = {$memo_sql}
            where id = {$id}";
  return sql_query($sql);
}

function delete_consult($id)
{
  $sql = "delete from cn_consult where id = {$id}";
  elog($sql);

  return sql_query($sql);
}
