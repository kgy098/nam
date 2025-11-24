<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_COMMUNITY_USE === false) {
  include_once(G5_THEME_MSHOP_PATH . '/index.php');
  return;
}

include_once(G5_THEME_MOBILE_PATH . '/head.php');
?>

  <!-- 상단 로고 영역 -->
  <header class="top-bar">
    <div class="logo-wrap">
      <img src="icons/logo_left.png" class="logo-left" alt="logo">
    </div>
    <button class="menu-btn">
      <img src="icons/menu.png" alt="menu">
    </button>
  </header>

  <!-- 캘린더 헤더 -->
  <section class="calendar-header">
    <button id="prevMonth" class="nav-btn">&lt;</button>
    <span id="currentMonth"></span>
    <button id="nextMonth" class="nav-btn">&gt;</button>
  </section>

  <!-- 캘린더 -->
  <section class="calendar">
    <div class="calendar-weekdays">
      <div>일</div><div>월</div><div>화</div><div>수</div><div>목</div><div>금</div><div>토</div>
    </div>
    <div id="calendarDays" class="calendar-days"></div>
  </section>

  <!-- 메인 버튼 4개 -->
  <section class="quick-menu">
    <div class="menu-btn-box">
      <img src="icons/check.png">
      <span>출결</span>
    </div>

    <div class="menu-btn-box">
      <img src="icons/calendar.png">
      <span>스터디카페 예약</span>
    </div>

    <div class="menu-btn-box">
      <img src="icons/doc.png">
      <span>학과상담 신청</span>
    </div>

    <div class="menu-btn-box">
      <img src="icons/chat.png">
      <span>멘토 상담</span>
    </div>
  </section>

  <!-- 하단 네비 -->
  <nav class="bottom-nav">
    <div class="nav-item active">
      <img src="icons/home.png">
      <span>홈</span>
    </div>

    <div class="nav-item">
      <img src="icons/check.png">
      <span>학습&출결</span>
    </div>

    <div class="nav-item">
      <img src="icons/chat.png">
      <span>상담&예약</span>
    </div>

    <div class="nav-item">
      <img src="icons/calendar.png">
      <span>학사일정&공지</span>
    </div>
  </nav>


<script src="<?=G5_THEME_JS_URL?>/nam.js"></script>

<script>

</script>


<?php
include_once(G5_THEME_MOBILE_PATH . '/tail.php');
