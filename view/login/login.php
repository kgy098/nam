<?php
include_once('./_common.php');
$g5['title'] = "인증";
include_once('../head.php');

// redirect 파라미터 전달 (없으면 빈 값)
$redirect = $_GET['redirect'] ?? '';
?>

<div class="common-section login-wrap">

  <div class="login-desc">
    전화번호와 회원가입 시 발급받은 인증번호를 입력해주세요.
  </div>

  <!-- 로그인 폼 시작 -->
  <form action="/view/login/login_check.php" method="post" id="loginForm">

    <input type="hidden" name="redirect" value="<?= $redirect ?>">

    <!-- 전화번호 -->
    <div class="common-row">
      <span class="common-row-label">전화번호</span>
      <input type="tel" name="mb_hp" id="mb_hp"
        class="common-input login-input" placeholder="010-0000-0000">
    </div>

    <!-- 인증번호 -->
    <div class="common-row">
      <span class="common-row-label">인증번호</span>
      <input type="text" name="auth_no" id="auth_no"
        class="common-input login-input" placeholder="인증번호 8자리">
    </div>

    <!-- 인증 버튼 -->
    <div class="common-row" style="margin-top: 20px;">
      <button type="submit" class="common-btn">인증</button>
    </div>

  </form>
  <!-- 로그인 폼 끝 -->

  <!-- 로고 -->
  <div class="login-logo-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/logo.png">
  </div>

</div>

<script>
  // 폼 유효성 체크
  $("#loginForm").on("submit", function (e) {
    const hp = $("#mb_hp").val().trim();
    const auth = $("#auth_no").val().trim();

    if (!hp) {
      alert("전화번호를 입력하세요.");
      $("#mb_hp").focus();
      return false;
    }

    if (!auth) {
      alert("인증번호를 입력하세요.");
      $("#auth_no").focus();
      return false;
    }

    return true;
  });
</script>
