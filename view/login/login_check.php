<?php
include_once('./_common.php');

// 입력값
$hp      = trim($_POST['mb_hp'] ?? '');
$auth_no = trim($_POST['auth_no'] ?? '');
$redirect = trim($_REQUEST['redirect'] ?? '');

$hp = preg_replace('/[^0-9]/', '', $hp);

// 값 체크
if ($hp === '' || $auth_no === '') {
  jres(false, '전화번호와 인증번호를 입력해주세요.');
}

// 회원 조회
$sql = "
    SELECT *
    FROM g5_member
    WHERE mb_hp = '{$hp}'
      AND auth_no = '{$auth_no}'
    LIMIT 1
";
$member = sql_fetch($sql);

if (!$member) {
  alert('전화번호 또는 인증번호가 일치하지 않습니다.');
}

// 탈퇴 여부 체크
if ($member['mb_leave_date'] && $member['mb_leave_date'] !== '0000-00-00') {
  alert('탈퇴한 회원입니다.');
}

// 차단 여부 체크
if ($member['mb_intercept_date'] && $member['mb_intercept_date'] !== '0000-00-00') {
  alert('접근이 차단된 회원입니다.');
}

/* ===========================
      로그인 성공 처리
=========================== */

// 세션 저장
// set_session('ss_mb_id', $member['mb_id']);
// set_session('ss_mb_key', md5($member['mb_hp'] . $_SERVER['REMOTE_ADDR']));

$_SESSION['ss_mb_id'] = $member['mb_id'];
$_SESSION['ss_mb_key'] = md5($member['mb_hp'] . $_SERVER['REMOTE_ADDR']);

set_cookie('ck_mb_id', $member['mb_id'], 86400 * 30);
$ck_auto = md5($_SERVER['SERVER_ADDR'] . $_SERVER['SERVER_SOFTWARE'] . $_SERVER['HTTP_USER_AGENT'] . $mb['mb_password']);
set_cookie('ck_auto', $ck_auto, 86400 * 30);

// 로그인 기록 업데이트
sql_query("
    UPDATE g5_member
    SET mb_login_ip = '{$_SERVER['REMOTE_ADDR']}',
        mb_login_dt = NOW()
    WHERE mb_id = '{$member['mb_id']}'
");

// 이동 URL 결정
$go = ($redirect !== '') ? G5_URL . $redirect : G5_URL . "/view/index.php";

// 성공 응답
// jres(true, ['redirect' => $go]);
goto_url($go);
