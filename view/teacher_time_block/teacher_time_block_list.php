<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "질문잠금 관리";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') {
  alert("로그인이 필요합니다.");
}
?>

<link rel="stylesheet" href="<?= G5_THEME_URL ?>/nam/css/common.css">

<!-- 안내문 -->
<div class="consult-info" style="margin-top:20px;">
  시간을 선택하여 질문잠금 시간을 설정할 수 있습니다.
</div>

<!-- 날짜 선택 -->
<div class="consult-top-section" style="margin-top:15px;">
  <div class="common-form-row first-row consult-top-row">
    <input type="date" id="selDate" class="common-input-date" style="flex:1 1 auto;">
    <button id="btnHoliday" class="consult-sheet-btn"
      style="height:38px;padding:0 12px;white-space:nowrap;">
      휴일 설정
    </button>
  </div>
</div>

<!-- 상태 안내 -->
<div class="consult-state-wrap" style="margin-top:15px;">
  <div class="consult-state-row">
    <div class="consult-state-dot consult-dot-disabled"></div> 질문잠금 시간
  </div>
  <div class="consult-state-row">
    <div class="consult-state-dot consult-dot-mine"></div> 질문가능 시간
  </div>
  <div class="consult-state-row" style="flex:1 1 auto; justify-content: flex-end;">
    <button id="btnRefresh" class="consult-sheet-btn" style="flex:0 1 auto; height:30px;padding:0 12px;white-space:nowrap; font-size:12px;">
      새로 고침
    </button>
  </div>
</div>

<!-- 시간표 -->
<div id="timeGrid" class="consult-time-grid" style="margin-top:5px;"></div>

<script src="<?= G5_API_URL ?>/api_teacher_time_block.js"></script>

<script>
var mb_id = "<?= $mb_id ?>";
var selectedDate = '';
var pendingDt = '';
var pendingBreakId = null; // MOD 잠금해제용 break_id 저장

$(document).ready(function() {
  initScreen();
});

function initScreen() {
  var today = new Date().toISOString().substring(0, 10);
  selectedDate = today;
  $('#selDate').val(today);

  loadSlots();

  $('#selDate').on('change', function() {
    selectedDate = $(this).val() || '';
    loadSlots();
  });

  $('#btnHoliday').on('click', function() {
    openAppModal(
      selectedDate + '<br><b>전체 잠금(휴일)</b>으로 설정하시겠습니까?',
      function () {
        setHoliday();
      }
    );
  });

  $('#btnRefresh').on('click', function () {
    loadSlots();
  });
}

/* ==================================
   슬롯 불러오기
================================== */
function loadSlots() {
  if (!selectedDate) return;

  TeacherTimeBlockAPI.slots({
    mb_id: mb_id,
    target_date: selectedDate
  }).then(function(res) {
    renderSlots(res.data || []);
  }).fail(function(err) {
    console.log(err);
    $('#timeGrid').html('');
  });
}

/* ==================================
   슬롯 렌더링
================================== */
function renderSlots(slots) {
  var html = '';

  slots.forEach(function(s) {
    var cls = 'consult-time-slot';
    var status = '';

    if (s.is_break) {
      cls += ' disabled';
      status = '잠금';
    } else {
      cls += ' mine';
      status = '가능';
    }

    html +=
      '<div class="' + cls + '" ' +
      'data-dt="' + s.scheduled_dt + '" ' +
      'data-status="' + status + '" ' +
      'data-break-id="' + (s.break_id || '') + '"' + // MOD
      '>' +
        s.time +
      '</div>';
  });

  $('#timeGrid').html(html);

  $('#timeGrid .consult-time-slot').each(function() {
    $(this).off('click').on('click', function() {
      var status = $(this).data('status');
      var dt = $(this).data('dt');
      var breakId = $(this).data('break-id'); // MOD

      pendingDt = dt;
      pendingBreakId = breakId; // MOD

      // 잠금 해제
      if (status === '잠금') {
        openAppModal(
          '<b>' + dt.substring(11,16) + '</b><br>이 시간을 잠금 해제할까요?',
          function() {
            unlockTime(); // MOD
          }
        );
      }
      // 잠금 설정
      else {
        openAppModal(
          '<b>' + dt.substring(11,16) + '</b><br>이 시간을 질문잠금으로 설정할까요?',
          function() {
            lockTime();
          }
        );
      }
    });
  });
}

/* ==================================
   잠금 설정
================================== */
function lockTime() {
  TeacherTimeBlockAPI.add({
    mb_id: mb_id,
    target_date: selectedDate,
    start_time: pendingDt.substring(11, 16),
    end_time: getNextTime(pendingDt.substring(11, 16)),
    ttb_type: 'BREAK'
  }).then(function() {
    loadSlots();
  });
}

/* ==================================
   잠금 해제 (remove 사용)
================================== */
function unlockTime() {
  if (!pendingBreakId) {
    alert("break_id 가 없어 잠금해제를 할 수 없습니다.");
    return;
  }

  TeacherTimeBlockAPI.remove(pendingBreakId)   // MOD remove 사용
    .then(function() {
      loadSlots();
    });
}

/* ==================================
   전체 잠금(휴일)
================================== */
function setHoliday() {
  if (!selectedDate) return;

  TeacherTimeBlockAPI.slots({
    mb_id: mb_id,
    target_date: selectedDate
  }).then(function(res) {

    var slots = res.data || [];

    slots.forEach(function(s) {
      var next = getNextTime(s.time);
      if (!next) return;

      TeacherTimeBlockAPI.add({
        mb_id: mb_id,
        target_date: selectedDate,
        start_time: s.time,
        end_time: next,
        ttb_type: 'BREAK'
      });
    });

    loadSlots();
  });
}

/* ==================================
   30분 후 계산
================================== */
function getNextTime(t) {
  var parts = t.split(":");
  var h = parseInt(parts[0], 10);
  var m = parseInt(parts[1], 10);

  m += 30;
  if (m >= 60) { h++; m -= 60; }

  if (h >= 23 && m > 0) return null;

  return (h < 10 ? "0"+h : h) + ":" + (m < 10 ? "0"+m : m);
}
</script>

<?php include_once('../tail.php'); ?>