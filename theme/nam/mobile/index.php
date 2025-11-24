<?php
if (!defined('_GNUBOARD_'))
  exit; // 개별 페이지 접근 불가

if (G5_COMMUNITY_USE === false) {
  include_once(G5_THEME_MSHOP_PATH . '/index.php');
  return;
}

include_once(G5_THEME_MOBILE_PATH . '/head.php');
?>

<!-- 상단 로고 영역 -->
<header class="top-bar">
  <div class="logo-wrap">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/logo.png" class="logo-left" alt="logo">
  </div>
  <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/menu.png" class="menu-btn" alt="menu">
</header>

<div class="notice-bar">
  <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/notice.png" class="notice-icon">
  <span class="notice-text">남안성비상에듀기숙학원 앱이 오픈되었습니다.</span>
</div>

<!-- 캘린더 헤더 -->
<div class="calendar-header">
  <span id="currentMonth" class="month-text"></span>
  <div class="arrow-wrap">
    <img id="prevMonth" class="nav-arrow-img" src="<?= G5_THEME_IMG_URL ?>/nam/ico/left.png" alt="Prev">
    <img id="nextMonth" class="nav-arrow-img" src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" alt="Next">
  </div>
</div>

<!-- 캘린더 -->
<section class="calendar">
  <div class="calendar-weekdays">
    <div>일</div>
    <div>월</div>
    <div>화</div>
    <div>수</div>
    <div>목</div>
    <div>금</div>
    <div>토</div>
  </div>
  <div id="calendarDays" class="calendar-days"></div>
</section>

<!-- 메인 버튼 4개 -->
<section class="quick-menu">
  <div class="menu-btn-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/attendance 2.png">
    <span>출결</span>
  </div>

  <div class="menu-btn-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/calendar2.png">
    <span>스터디카페 예약</span>
  </div>

  <div class="menu-btn-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/counsel3.png">
    <span>학과상담 신청</span>
  </div>

  <div class="menu-btn-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/mentor4.png">
    <span>멘토 상담</span>
  </div>
</section>

<!-- 하단 네비 -->
<nav class="bottom-nav">
  <div class="nav-item active">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/home_pressed2.png">
    <span>홈</span>
  </div>

  <div class="nav-item">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/book2.png">
    <span>학습&출결</span>
  </div>

  <div class="nav-item">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/message2.png">
    <span>상담&예약</span>
  </div>

  <div class="nav-item">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/calendar3.png">
    <span>학사일정&공지</span>
  </div>
</nav>


<!-- dim 배경 -->
<div id="scheduleDim" class="schedule-dim"></div>

<!-- 일정 팝업 -->
<div id="schedulePopup" class="schedule-popup">
  <div class="popup-header">
    <span id="popupDate"></span>
    <button id="popupClose" class="popup-close">✕</button>
  </div>

  <div id="popupList" class="popup-list"></div>
</div>



<script src="<?= G5_API_URL ?>/api_schedule.js"></script>
<script src="<?= G5_THEME_JS_URL ?>/nam.js"></script>

<script>
function openSchedulePopup(dateStr, schedules) {
  const dim = document.getElementById("scheduleDim");
  const popup = document.getElementById("schedulePopup");
  const popupDate = document.getElementById("popupDate");
  const popupList = document.getElementById("popupList");

  // 날짜 표시
  popupDate.innerText = dateStr;

  // 목록 구성
  popupList.innerHTML = "";
  if (schedules.length === 0) {
    popupList.innerHTML = `<div class="schedule-item">일정 없음</div>`;
  } else {
    schedules.forEach(item => {
      popupList.innerHTML += `
        <div class="schedule-item">
          ${item.title}
        </div>`;
    });
  }

  // 레이어 표시
  dim.style.display = "block";
  popup.style.display = "block";

  // bottom-sheet 애니메이션
  setTimeout(() => {
    popup.style.bottom = "0";
  }, 10);
}

function closeSchedulePopup() {
  const dim = document.getElementById("scheduleDim");
  const popup = document.getElementById("schedulePopup");

  popup.style.bottom = "-60%";
  setTimeout(() => {
    popup.style.display = "none";
    dim.style.display = "none";
  }, 200);
}

document.getElementById("popupClose").addEventListener("click", closeSchedulePopup);
document.getElementById("scheduleDim").addEventListener("click", closeSchedulePopup);

</script>


<?php
include_once(G5_THEME_MOBILE_PATH . '/tail.php');
