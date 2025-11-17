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
