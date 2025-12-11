<?
include_once('./_common.php');
if (!defined('_GNUBOARD_')) exit;
$menu_group = 'cal';
$g5['title'] = "학사일정";
include_once('../head.php');


?>

<div class="calendar-header">
  <span id="currentMonth" class="month-text"></span>
  <div class="arrow-wrap">
    <img id="prevMonth" src="<?= G5_THEME_IMG_URL ?>/nam/ico/left.png" class="nav-arrow-img">
    <img id="nextMonth" src="<?= G5_THEME_IMG_URL ?>/nam/ico/right.png" class="nav-arrow-img">
  </div>

  <button id="btnRefresh" class="consult-sheet-btn" style="flex:0 1 auto; height:30px;padding:0 12px;white-space:nowrap; font-size:12px;">
    새로 고침
  </button>

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
  $("#btnRefresh").on("click", function() {
    loadSchedule(currentYear, currentMonth).then(renderCalendar);
  });

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
include_once('../tail.php');
