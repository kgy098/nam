<?
include_once('./_common.php');
if (!defined('_GNUBOARD_')) exit;

$g5['title'] = "main";
include_once('./head.php');


?>

<div class="notice-bar">
  <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/notice.png" class="notice-icon">
  <span class="notice-label">공지</span>
  <span class="notice-text">남안성비상에듀기숙학원 앱이 오픈되었습니다.</span>
</div>

<div class="calendar-header">
  <span id="currentMonth" class="month-text"></span>
  <div class="arrow-wrap">
    <img id="prevMonth" src="<?= G5_THEME_IMG_URL ?>/nam/ico/left.png" class="nav-arrow-img">
    <img id="nextMonth" src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="nav-arrow-img">
  </div>
</div>

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

<section class="quick-menu">
  <div class="menu-btn-box left-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/attendance 2.png">
    <span>출결</span>
  </div>

  <div class="menu-btn-box right-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/calendar2.png">
    <span>스터디라운지 예약</span>
  </div>

  <div class="menu-btn-box left-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/counsel3.png">
    <span>학과상담 신청</span>
  </div>

  <div class="menu-btn-box right-box">
    <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/mentor4.png">
    <span>멘토 상담</span>
  </div>
</section>


<div id="scheduleDim" class="schedule-dim"></div>

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
    $("#popupDate").text(dateStr);
    $("#popupList").html(
      schedules.length === 0 ?
      "<div class='schedule-item'>일정 없음</div>" :
      schedules.map(v => `<div class="schedule-item">${v.title}</div>`).join('')
    );

    $("#scheduleDim").show();
    $("#schedulePopup").show();
    setTimeout(() => $("#schedulePopup").css("bottom", "0"), 10);
  }

  function closeSchedulePopup() {
    $("#schedulePopup").css("bottom", "-60%");
    setTimeout(() => {
      $("#schedulePopup").hide();
      $("#scheduleDim").hide();
    }, 200);
  }

  $("#popupClose, #scheduleDim").on("click", closeSchedulePopup);

</script>


<?
include_once('./tail.php');
