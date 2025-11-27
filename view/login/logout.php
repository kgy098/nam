<?php
include_once('./_common.php');

// 세션 삭제
unset($_SESSION['ss_mb_id']);
unset($_SESSION['ss_mb_key']);

// 자동로그인 쿠키 삭제
set_cookie('ck_mb_id', '', 0);
set_cookie('ck_auto', '', 0);

// 리다이렉트 파라미터
$redirect = trim($_REQUEST['redirect'] ?? '');

// 이동 URL
$go = ($redirect !== '')
    ? G5_URL . $redirect
    : G5_URL . "/view/login/login.php";

// 페이지 이동
goto_url($go);

?>
