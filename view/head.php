<?
if (!defined('_GNUBOARD_')) exit;

$no_login_check = [
  '/view/login/login.php',
  '/view/login/login_check.php'
];

// 현재 요청 URI (쿼리스트링 제거)
$current_path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$current_full = $_SERVER['REQUEST_URI']; // 쿼리스트링 포함

// 로그인 체크 제외 페이지가 아닌 경우
if (!in_array($current_path, $no_login_check, true)) {

  // 로그인 세션 없으면 로그인 페이지로 이동
  if (empty($_SESSION['ss_mb_id']) || !$is_member) {
    goto_url('/view/login/login.php?redirect=' . urlencode($current_full));
    exit;
  }
}


// 기본 제목 처리
$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

if (!isset($g5['title'])) {
  $g5['title'] = $config['cf_title'];
  $g5_head_title = $g5['title'];
} else {
  $g5_head_title = implode(' | ', array_filter([$g5['title'], $config['cf_title']]));
}

$g5['title'] = strip_tags($g5['title']);
$g5_head_title = strip_tags($g5_head_title);

// 접속자 위치 기록
$g5['lo_location'] = addslashes($g5['title']);
if (!$g5['lo_location'])
  $g5['lo_location'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
$g5['lo_url'] = addslashes(clean_xss_tags($_SERVER['REQUEST_URI']));
if (strstr($g5['lo_url'], '/' . G5_ADMIN_DIR . '/') || $is_admin == 'super')
  $g5['lo_url'] = '';


?>
<!doctype html>
<html lang="ko">

<head>
  <meta charset="utf-8">

  <? if (G5_IS_MOBILE) { ?>
    <meta name="viewport" content="width=device-width,initial-scale=1.0">
    <meta name="format-detection" content="telephone=no">
  <? } else { ?>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
  <? } ?>

  <title><?= $g5_head_title ?></title>

  <script>
    var g5_url = "<?= G5_URL ?>";
    var g5_api_url = "<?= G5_API_URL ?>";
    var g5_ctrl_url = "<?= G5_CTRL_URL ?>";
    var g5_view_url = "<?= G5_VIEW_URL ?>";
    var g5_is_member = "<?= $is_member ?>";
    var g5_is_admin = "<?= $is_admin ?>";
  </script>

  <!-- CSS -->
  <link rel="stylesheet" href="<?= G5_THEME_CSS_URL ?>/default.css">
  <link rel="stylesheet" href="<?= G5_THEME_CSS_URL ?>/nam.css">
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard/dist/web/static/pretendard.css"> -->
  <!-- <link rel="stylesheet" href="https://cdn.jsdelivr.net/gh/orioncactus/pretendard@v1.3.8/dist/web/static/pretendard-subset.css"> -->
  <link rel="stylesheet" href="<?= G5_THEME_CSS_URL ?>/pretendard.css">

  <!-- JS 직접 삽입 -->
  <script src="<?= G5_JS_URL ?>/jquery-1.12.4.min.js"></script>
  <script src="<?= G5_JS_URL ?>/jquery-migrate-1.4.1.min.js"></script>
  <script src="<?= G5_JS_URL ?>/common.js"></script>
  <script src="<?= G5_JS_URL ?>/wrest.js"></script>

</head>

<body>

  <? if ($g5['title'] == "main") { ?>
    <header class="top-bar">
      <img src="<?= G5_THEME_IMG_URL ?>/nam/logo.png" class="top-logo">
      <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/menu.png" id="hamburgerBtn" class="menu-btn">
    </header>
  <? } else if ($g5['title'] == "인증") { ?>
    <header class="top-bar sub-header">
      <span class="sub-header-title">인증</span>
    </header>
  <? } else { ?>
    <header class="top-bar sub-header">
      <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/left.png" class="top-logo" onclick="history.back();">
      <span class="sub-header-title"><?= $g5['title'] ?></span>
      <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/menu.png" id="hamburgerBtn" class="menu-btn">
    </header>
  <? } ?>


  <? include_once(G5_VIEW_PATH . "/sidemenu.php"); ?>