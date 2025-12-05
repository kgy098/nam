<?


function jres($ok, $data = null)
{
  echo json_encode(['result' => $ok ? 'SUCCESS' : 'FAIL', 'data' => $data], JSON_UNESCAPED_UNICODE);
  exit;
}
function esc($s)
{
  if (function_exists('sql_escape_string')) {
    return sql_escape_string($s);
  }

  return addslashes($s);
}

/**
 * 공통 파일 업로드 함수
 * 
 * @param string $input_name   <input type="file" name="...">
 * @param string $upload_dir   저장될 경로(절대경로)
 * @param array  $allowed_ext  허용 확장자
 * @param int    $max_size     최대 용량(bytes)
 *
 * @return array|false 업로드 성공 시 파일 정보, 실패 시 false
 */
function file_upload($input_name, $upload_dir, $allowed_ext = ['jpg', 'jpeg', 'png', 'pdf', 'zip', 'xls', 'xlsx'], $max_size = 10485760)
{
  // error_log(__FILE__ . __LINE__ . "\n _FILES: " . print_r($_FILES, true));
  // error_log(__FILE__ . __LINE__ . "\n upload_dir13131321: " . $upload_dir);

  if (!isset($_FILES[$input_name]) || empty($_FILES[$input_name]['name'])) {
    return false;
  }
  // error_log(__FILE__ . __LINE__ . "\n upload_dir0: " . $upload_dir);

  $file = $_FILES[$input_name];
  if ($file['error'] !== UPLOAD_ERR_OK) {
    return false;
  }

  $name = $file['name'];
  $tmp = $file['tmp_name'];
  $size = $file['size'];

  $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));

  // 확장자 체크
  if (!in_array($ext, $allowed_ext)) {
    return false;
  }
  // error_log(__FILE__ . __LINE__ . "\n upload_dir1: " . $upload_dir);

  // 용량 체크
  if ($size > $max_size) {
    return false;
  }
  // error_log(__FILE__ . __LINE__ . "\n upload_dir2: " . $upload_dir);

  // 디렉토리 없으면 생성
  if (!is_dir($upload_dir)) {
    @mkdir($upload_dir, 0777, true);
  }

  // 서버 저장 파일명 생성
  $new_name = md5(uniqid('', true)) . '.' . $ext;
  $save_path = rtrim($upload_dir, '/') . '/' . $new_name;

  // error_log(__FILE__ . __LINE__ . "\n save_path: " . $save_path);

  // 실제 파일 이동
  if (!move_uploaded_file($tmp, $save_path)) {
    return false;
  }
  // error_log(__FILE__.__LINE__."\n save_path112131: " . $save_path);

  // 이미지 정보
  $width = 0;
  $height = 0;
  if (in_array($ext, ['jpg', 'jpeg', 'png'])) {
    $img = @getimagesize($save_path);
    if ($img) {
      $width = $img[0];
      $height = $img[1];
    }
  }

  return [
    'bf_source'   => $name,
    'bf_file'     => $new_name,
    'bf_filesize' => $size,
    'bf_width'    => $width,
    'bf_height'   => $height,
    'bf_ext'      => $ext,
    'bf_path'     => $save_path
  ];
}


/* ============================================================
 * 시간표 생성 (slot builder)
 * ============================================================ */
function _build_time_slots($teacher_mb_id, $target_date, $student_mb_id, $mode = 'student')
{
  $slots = [];
  $start = strtotime("{$target_date} 07:00:00");
  $end   = strtotime("{$target_date} 23:00:00");

  while ($start < $end) {
    $t = date('H:i', $start);
    $slots[] = [
      'time'         => $t,
      'status'       => '상담가능',
      'exists'       => false,
      'is_break'     => false,   
      'is_reserved'  => false, 
      'scheduled_dt' => date('Y-m-d H:i:s', $start),
      'mb_name'      => '',
      'class_name'   => '',
      'break_id'     => '',
    ];
    $start = strtotime("+30 minutes", $start);
  }

  /* ================================
   * 휴게시간(BREAK) 처리
   * ================================ */
  $blocks = select_teacher_time_block_by_teacher_date($teacher_mb_id, $target_date);

  foreach ($blocks as $b) {
    foreach ($slots as &$s) {
      $cur = strtotime("{$target_date} {$s['time']}:00");

      if (
        $cur >= strtotime("{$target_date} {$b['start_time']}") &&
        $cur <  strtotime("{$target_date} {$b['end_time']}")
      ) {
        $s['break_id'] = $b['id'];
        $s['is_break'] = true;
            
        if ($mode === 'teacher') {
          // 선생님 화면
          if ($b['type'] === 'BREAK') {
            $s['status'] = '휴게시간';
          }
        } else {
          // 학생 화면 기존 로직
          if ($b['type'] === 'BREAK' ) {
            $s['status'] = '상담불가';
          }
        }
      }
    }
    unset($s);
  }

  /* ================================
   * 예약 반영
   * ================================ */
  $reserved = select_consult_by_teacher_and_date(
    $teacher_mb_id,
    $_REQUEST['consult_type'],
    $target_date
  );

  foreach ($reserved as $r) {
    foreach ($slots as &$s) {
      if ($s['scheduled_dt'] === $r['scheduled_dt']) {
        $s['is_reserved']  = true;

        $s['exists'] = true;
        $s['mb_name'] = $r['mb_name'];  // 학생 이름
        $s['class_name'] = $r['class_name'];
        $s['consult_type'] = $r['type']; 
        $s['consult_id'] = $r['id']; 

        if ($mode === 'teacher') {
          // 선생님 화면: 예약되었으면 무조건 예약완료
          $s['status'] = '예약완료';
        } else {
          // 학생 화면: 기존 mine 처리
          if ($r['student_mb_id'] === $student_mb_id) {
            $s['status'] = '내상담';
            $s['mine'] = true;
          } else {
            $s['status'] = '상담불가';
          }
        }
      }
    }

    // elog
    unset($s);
  }

  return $slots;
}

function build_teacher_slots_common($teacher_mb_id, $target_date) {
    return _build_time_slots($teacher_mb_id, $target_date, null, 'teacher');
}