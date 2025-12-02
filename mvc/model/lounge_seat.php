<?php

function select_lounge_seat_list($start = 0, $num = CN_PAGE_NUM)
{
  $start = (int)$start;
  $num   = (int)$num;

  $sql = "
        SELECT
            s.*,
            l.name AS lounge_name,
            l.location AS lounge_location,
            l.total_seats AS lounge_total_seats
        FROM cn_lounge_seat AS s
        LEFT JOIN cn_lounge AS l ON s.lounge_id = l.id
        ORDER BY l.id ASC, s.cell_no ASC, s.id DESC
        LIMIT {$start}, {$num}
    ";
  $result = sql_query($sql);
  if ($result === false) return false;

  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_lounge_seat_listcnt()
{
  $row = sql_fetch("SELECT COUNT(id) AS cnt FROM cn_lounge_seat");
  return isset($row['cnt']) ? (int)$row['cnt'] : 0;
}

function select_lounge_seat_one($id)
{
  $id = (int)$id;

  $sql = "
        SELECT
            s.*,
            l.name AS lounge_name,
            l.location AS lounge_location,
            l.total_seats AS lounge_total_seats
        FROM cn_lounge_seat AS s
        LEFT JOIN cn_lounge AS l ON s.lounge_id = l.id
        WHERE s.id = {$id}
    ";
  return sql_fetch($sql);
}

function select_lounge_seat_by_lounge($lounge_id, $only_active = false, $start = 0, $num = CN_PAGE_NUM)
{
  $lounge_id = (int)$lounge_id;
  $start     = (int)$start;
  $num       = (int)$num;

  $where = "s.lounge_id = {$lounge_id}";
  if ($only_active) $where .= " AND s.is_active = 1";

  $sql = "
        SELECT
            s.*,
            l.name AS lounge_name,
            l.location AS lounge_location,
            l.total_seats AS lounge_total_seats
        FROM cn_lounge_seat AS s
        LEFT JOIN cn_lounge AS l ON s.lounge_id = l.id
        WHERE {$where}
        ORDER BY s.cell_no ASC, s.id DESC
        LIMIT {$start}, {$num}
    ";
  $result = sql_query($sql);
  elog("SQL: $sql");
  if ($result === false) return false;

  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function insert_lounge_seat($lounge_id, $cell_no, $seat_no, $is_active = 1)
{
  $lounge_id = (int)$lounge_id;
  $cell_no   = (int)$cell_no;
  $seat_no   = trim($seat_no);
  $is_active = (int)$is_active;

  $sql = "
        INSERT INTO cn_lounge_seat
        SET lounge_id = {$lounge_id},
            cell_no   = {$cell_no},
            seat_no   = '{$seat_no}',
            is_active = {$is_active}
    ";
  return sql_query($sql);
}

function update_lounge_seat($id, $lounge_id, $cell_no, $seat_no, $is_active)
{
  $id        = (int)$id;
  $lounge_id = (int)$lounge_id;
  $cell_no   = (int)$cell_no;
  $seat_no   = trim($seat_no);
  $is_active = (int)$is_active;

  $sql = "
        UPDATE cn_lounge_seat
        SET lounge_id = {$lounge_id},
            cell_no   = {$cell_no},
            seat_no   = '{$seat_no}',
            is_active = {$is_active}
        WHERE id = {$id}
    ";
  return sql_query($sql);
}

function delete_lounge_seat($id)
{
  $id = (int)$id;

  $sql = "DELETE FROM cn_lounge_seat WHERE id = {$id}";
  return sql_query($sql);
}
