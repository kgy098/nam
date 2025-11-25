<?php
// 로그인 여부 체크
$is_login = isset($member['mb_id']) && $member['mb_id'] !== '';
$mb_name = $is_login ? $member['mb_name'] : '';
?>
<div id="drawer-overlay"></div>

<div id="drawer">
  <div class="drawer-header">
    <? if (!$is_login) { ?>
      <div class="drawer-title">로그인을 해주세요.</div>
      <a href="/login.php" class="drawer-login-btn">
        <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/login.png" class="btn-img">
      </a>
    <? } else { ?>
      <div class="drawer-title"><?= $mb_name ?>님 반갑습니다.</div>
      <a href="/logout.php" class="drawer-login-btn">
        <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/logout.png" class="btn-img">
      </a>
    <? } ?>

    <!-- X 닫기 버튼 -->
    <button id="drawerCloseBtn" class="drawer-close">
      <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/close.png" class="btn-img">
    </button>
  </div>


  <div class="drawer-menu">
    <a href="/notice.php">공지사항</a>
    <a href="/schedule.php">학사일정</a>
    <a href="/lounge_reserve.php">스터디 라운지 예약</a>
    <a href="/consult_subject.php">학과 상담</a>
    <a href="/consult_mentor.php">멘토 상담</a>
    <a href="/qna.php">비대면 질의 응답</a>
    <a href="/video.php">수업 영상</a>
    <a href="/report.php">학습 보고서</a>
    <a href="/mock_apply.php">모의고사 신청</a>
    <a href="/attendance.php">출결 관리</a>
    <a href="/pay.php">학원비 납부 & 내역</a>

    <?php if ($is_login) { ?>
      <a href="/myinfo.php">내 정보 변경</a>
    <?php } ?>
  </div>

  <div class="drawer-footer">
    <div class="corp">(주)팡스카이에듀</div>
    <div class="links">
      <a href="/privacy.php">개인정보처리방침</a>
      <span> | </span>
      <a href="/terms.php">이용약관</a>
    </div>
    <div class="corp-info">
      주소 : 충남 태안군 안면읍 백사장 2길 25-60<br>
      전화 : 000-000-0000<br>
      이메일 : 000@000.com<br>
      사업자등록번호 : 261-81-20861
    </div>
  </div>
</div>

<script>
  $(function() {

    // 메뉴 열기
    $("#hamburgerBtn").on("click", function() {
      $("#drawer").addClass("open");
      $("#drawer-overlay").addClass("show");
    });

    // X 버튼 클릭 시 닫기
    $("#drawerCloseBtn").on("click", function() {
      closeDrawer();
    });

    // 메뉴 바깥 영역 클릭 시 닫기
    $("#drawer-overlay").on("click", function() {
      closeDrawer();
    });

    function closeDrawer() {
      $("#drawer").removeClass("open");
      $("#drawer-overlay").removeClass("show");
    }

  });
</script>