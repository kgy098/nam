<?php
/* cn_lounge_reservation.php */

function select_lounge_reservation_list($filters = [], $start = 0, $num = CN_PAGE_NUM)
{
  $start = (int)$start;
  $num   = (int)$num;

  $where = "1=1";

  // -------------------------
  // 라운지 필터
  // -------------------------
  if (!empty($filters['lounge_id'])) {
    $lounge_id = (int)$filters['lounge_id'];
    $where .= " AND lr.lounge_id = {$lounge_id}";
  }

  // -------------------------
  // 날짜 필터 (예약일)
  // -------------------------
  if (!empty($filters['target_date'])) {
    $date = sql_escape_string($filters['target_date']);
    $where .= " AND lr.reserved_date = '{$date}'";
  }

  // -------------------------
  // 검색어 필터 (학생명, 좌석번호)
  // -------------------------
  if (!empty($filters['field']) && !empty($filters['keyword'])) {

    $field = $filters['field'];
    $keyword = sql_escape_string($filters['keyword']);

    if ($field === 'student_name') {
      $where .= " AND m.mb_name LIKE '%{$keyword}%'";
    } else if ($field === 'seat_no') {
      $where .= " AND ls.seat_no LIKE '%{$keyword}%'";
    }
  }

  // -------------------------
  // 메인 SQL
  // -------------------------
  $sql = "
    SELECT 
      lr.*,
      l.name AS lounge_name,
      ls.seat_no AS seat_no,
      m.mb_name AS student_name
    FROM cn_lounge_reservation lr
      LEFT JOIN cn_lounge l ON lr.lounge_id = l.id
      LEFT JOIN cn_lounge_seat ls ON lr.seat_id = ls.id
      LEFT JOIN g5_member m ON lr.mb_id = m.mb_id
    WHERE {$where}
    ORDER BY lr.reserved_date DESC, lr.start_time DESC, lr.id DESC
    LIMIT {$start}, {$num}
  ";

  elog($sql);

  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;

  return $list;
}


function select_lounge_reservation_listcnt($filters = [])
{
  $where = "1=1";

  if (!empty($filters['lounge_id'])) {
    $lounge_id = (int)$filters['lounge_id'];
    $where .= " AND lounge_id = {$lounge_id}";
  }

  if (!empty($filters['target_date'])) {
    $date = sql_escape_string($filters['target_date']);
    $where .= " AND reserved_date = '{$date}'";
  }

  if (!empty($filters['field']) && !empty($filters['keyword'])) {

    $field = $filters['field'];
    $keyword = sql_escape_string($filters['keyword']);

    if ($field === 'student_name') {
      $where .= " AND mb_id IN (SELECT mb_id FROM g5_member WHERE mb_name LIKE '%{$keyword}%')";
    } else if ($field === 'seat_no') {
      $where .= " AND seat_id IN (SELECT id FROM cn_lounge_seat WHERE seat_no LIKE '%{$keyword}%')";
    }
  }

  $row = sql_fetch("SELECT COUNT(*) AS cnt FROM cn_lounge_reservation WHERE {$where}");
  return (int)$row['cnt'];
}


function select_lounge_reservation_one($id)
{
  $sql = "select * from cn_lounge_reservation where id = {$id}";
  return sql_fetch($sql);
}

function select_lounge_reservation_by_student($mb_id, $start = 0, $num = CN_PAGE_NUM)
{
  // $sql = "select *
  //           from cn_lounge_reservation
  //           where mb_id = '{$mb_id}'
  //           order by reserved_date desc, start_time desc, id desc
  //           limit $start, $num";

  $sql = "select r.*, l.name as l_name, ls.seat_no as seat_no	
          from cn_lounge_reservation r
            LEFT JOIN cn_lounge l on l.id=r.lounge_id 
            LEFT JOIN cn_lounge_seat ls on ls.lounge_id=r.lounge_id and ls.id=r.seat_id 
          where mb_id = '{$mb_id}'
          order by r.reserved_date desc, r.start_time desc, r.id desc
          limit $start, $num";

  elog("$sql");
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function select_lounge_reservation_by_date($reserved_date, $lounge_id = null, $seat_id = null, $start = 0, $num = CN_PAGE_NUM)
{
  $where = "reserved_date = '{$reserved_date}'";
  if (!is_null($lounge_id)) $where .= " and lounge_id = {$lounge_id}";
  if (!is_null($seat_id))   $where .= " and seat_id = {$seat_id}";
  $sql = "select *
            from cn_lounge_reservation
            where {$where}
            order by start_time asc, id asc
            limit $start, $num";
  $result = sql_query($sql);
  $list = [];
  while ($row = sql_fetch_array($result)) $list[] = $row;
  return $list;
}

function count_reservation_by_mb_date($mb_id, $reserved_date)
{
  $mb_id = esc($mb_id);
  $reserved_date = esc($reserved_date);

  $sql = "
        SELECT COUNT(*) AS cnt
        FROM cn_lounge_reservation
        WHERE mb_id = '{$mb_id}'
          AND reserved_date = '{$reserved_date}'
          AND status = '예약'
    ";
  // elog("$sql");

  $row = sql_fetch($sql);
  return (int)$row['cnt'];
}

function exists_lounge_reservation($lounge_id, $seat_id, $reserved_date, $start_time)
{
  $lounge_id = (int)$lounge_id;
  $seat_id   = (int)$seat_id;
  $reserved_date = esc($reserved_date);
  $start_time    = esc($start_time);

  $sql = "
        SELECT id
        FROM cn_lounge_reservation
        WHERE lounge_id = '{$lounge_id}'
          AND seat_id   = '{$seat_id}'
          AND reserved_date = '{$reserved_date}'
          AND start_time    = '{$start_time}'
          AND status = '예약'
        LIMIT 1
    ";

  return sql_fetch($sql); // 있으면 array, 없으면 null
}


function insert_lounge_reservation($mb_id, $lounge_id, $seat_id, $reserved_date, $start_time, $end_time, $status = '예약')
{
  $sql = "insert into cn_lounge_reservation
            set mb_id = '{$mb_id}',
                lounge_id = {$lounge_id},
                seat_id = {$seat_id},
                reserved_date = '{$reserved_date}',
                start_time = '{$start_time}',
                end_time = '{$end_time}',
                status = '{$status}'";
  return sql_query($sql);
}

function update_lounge_reservation($id, $lounge_id, $seat_id, $reserved_date, $start_time, $end_time, $status)
{
  $sql = "update cn_lounge_reservation
            set lounge_id = {$lounge_id},
                seat_id = {$seat_id},
                reserved_date = '{$reserved_date}',
                start_time = '{$start_time}',
                end_time = '{$end_time}',
                status = '{$status}'
            where id = {$id}";
  return sql_query($sql);
}

function delete_lounge_reservation($id)
{
  $sql = "delete from cn_lounge_reservation where id = {$id}";
  return sql_query($sql);
}
