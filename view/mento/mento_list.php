<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "멘토상담";
include_once('../head.php');

// 로그인 사용자
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') {
  alert("로그인이 필요합니다.");
}

// 상담 종류 고정
$CONSULT_TYPE = "멘토상담";
?>

<style>
/* ================================
   학과상담 UI 재사용 (그대로)
   필요시 추가 스타일만 보완
================================ */
.common-select-box select {
  color: #ffffffe0;
}
.consult-top-row {
  width: 90%;
  max-width: 420px;
  margin: 12px auto 0;
  display: grid;
  grid-template-columns: 1fr 1fr 1fr;
  gap: 10px;
}

.consult-info {
  width: 90%;
  max-width: 420px;
  margin: 10px auto 0;
  font-size: 13px;
  color: #ADABA6E0;
  line-height: 1.5em;
}

.time-grid {
  width: 90%;
  max-width: 420px;
  margin: 14px auto 0;
  display: grid;
  grid-template-columns: repeat(4, 1fr);
  gap: 10px;
}
.time-cell {
  height: 46px;
  border-radius: 8px;
  background: #2b2c31;
  border: 1px solid #2d2d34;
  color: #D9D8D5;
  font-size: 14px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.time-cell.available { background: #3A3A3F; }
.time-cell.mine {
  background: #A17E36;
  color: #000;
  font-weight: 600;
}
.time-cell.disabled { opacity: 0.25; }
.time-cell.selected { border: 1px solid #E4CA92; }

/* bottom sheet */
.sheet-dim {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0, 0, 0, 0.4);
  z-index: 900;
}
.sheet-box {
  display: none;
  position: fixed;
  left: 0;
  bottom: -70%;
  width: 100%;
  max-height: 70%;
  background: #1B1B20;
  border-radius: 14px 14px 0 0;
  padding: 16px 20px 26px;
  z-index: 999;
  transition: bottom 0.25s ease;
}
.sheet-header {
  text-align: center;
  font-size: 16px;
  font-weight: 600;
  color: #E5C784;
  margin-bottom: 12px;
}
.sheet-btn-wrap {
  margin-top: 18px;
  display: flex;
  gap: 12px;
}
.sheet-btn {
  flex: 1;
  height: 46px;
  border-radius: 8px;
  background: #2A2A31;
  border: 1px solid #3A3A3F;
  font-size: 15px;
  color: #D9D8D5E0;
}
.sheet-btn.confirm {
  background: #A17E36;
  border: none;
  color: #000;
  font-weight: 600;
}
</style>

<!-- ======================================
     1) (선생님 / 날짜 / 시간 선택)
====================================== -->
<div class="consult-top-row">

  <!-- 선생님 선택 -->
  <div class="common-select-box">
    <select id="selTeacher" class="common-select"></select>
  </div>

  <!-- 날짜 -->
  <div class="common-select-box">
    <select id="selDate" class="common-select"></select>
  </div>

  <!-- 시간 -->
  <div class="common-select-box">
    <select id="selTime" class="common-select"></select>
  </div>
</div>

<!-- 안내문 -->
<div class="consult-info">
  상담 가능한 시간대를 선택하세요.
</div>

<!-- 시간대 표시 -->
<div id="timeGrid" class="time-grid"></div>

<!-- 내 상담 리스트 -->
<div class="common-list-container" id="myList"></div>
<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>

<!-- bottom sheet -->
<div class="sheet-dim" id="bookDim" onclick="closeBookSheet();"></div>
<div class="sheet-box" id="bookSheet">
  <div class="sheet-header">멘토상담 예약</div>
  <div style="padding:14px 0; text-align:center; font-size:15px; color:#fff;">
    이 시간으로 예약하시겠습니까?
  </div>
  <div class="sheet-btn-wrap">
    <button class="sheet-btn" onclick="closeBookSheet();">취소</button>
    <button class="sheet-btn confirm" onclick="reserveConsult();">예약하기</button>
  </div>
</div>

<script src="<?= G5_API_URL ?>/api_member.js"></script>
<script src="<?= G5_API_URL ?>/api_consult.js"></script>

<script>
var mb_id = "<?= $mb_id ?>";
var CONSULT_TYPE = "<?= $CONSULT_TYPE ?>";

var selectedTeacher = null;
var selectedDate = null;
var selectedTime = null;
var pageNum = 1;

/* ===============================
       초기 로딩
=============================== */
$(document).ready(function() {
  loadToday();
  loadDateOptions();
  loadTimeOptions();
  loadTeachers();

  $('#selTeacher').on('change', function() {
    selectedTeacher = $(this).val();
    markTimeStates();
    loadMyConsult(1, true);
  });

  $('#selDate').on('change', function() {
    selectedDate = $(this).val();
    markTimeStates();
    loadMyConsult(1, true);
  });

  $('#selTime').on('change', function() {
    selectedTime = $(this).val();
    markTimeStates();
  });
});

/* ===============================
      1) 선생님 목록
=============================== */
function loadTeachers() {
  memberAPI.list(1, 100).then(res => {
    let list = res.data.list || [];
    let html = '';

    list
      .filter(x => x.mb_level == 5) // 선생님만
      .forEach(t => {
        html += `<option value="${t.mb_id}">${t.mb_name}</option>`;
      });

    $('#selTeacher').html(html);

    if (list.length > 0) {
      selectedTeacher = list[0].mb_id;
      $('#selTeacher').val(selectedTeacher);
    }

    markTimeStates();
    loadMyConsult(1);
  });
}

/* ===============================
      2) 날짜 옵션
=============================== */
function loadToday() {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  selectedDate = `${y}-${m}-${day}`;
}
function loadDateOptions() {
  let html = '';
  const today = new Date();
  for (let i = 0; i < 7; i++) {
    const d = new Date();
    d.setDate(today.getDate() + i);
    const y = d.getFullYear();
    const m = String(d.getMonth() + 1).padStart(2, '0');
    const day = String(d.getDate()).padStart(2, '0');
    const val = `${y}-${m}-${day}`;
    html += `<option value="${val}">${m}/${day}</option>`;
  }
  $('#selDate').html(html).val(selectedDate);
}

/* ===============================
      3) 시간대 옵션
      (07:00 ~ 23:00)
=============================== */
function loadTimeOptions() {
  let html = '<option value="">시간선택</option>';
  for (let h = 7; h <= 23; h++) {
    let hh = String(h).padStart(2, '0') + ":00:00";
    html += `<option value="${hh}">${h}:00</option>`;
  }
  $('#selTime').html(html);
}


/* ===============================
      4) 시간대 상태 표시
=============================== */
function markTimeStates() {
  if (!selectedTeacher || !selectedDate) return;

  ConsultAPI.byTeacher(selectedTeacher, selectedDate).then(res => {
    renderTimeGrid(res.data || []);
  }).fail(() => {
    renderTimeGrid([]);
  });
}

function renderTimeGrid(list) {
  let html = '';
  let reservedMap = {};

  list.forEach(x => {
    reservedMap[x.scheduled_dt.substring(11, 19)] = x;
  });

  for (let h = 7; h <= 23; h++) {
    const hh = String(h).padStart(2, '0') + ":00:00";

    let cls = 'time-cell ';
    let label = h + ':00';

    if (!reservedMap[hh]) {
      cls += 'available';
    } else if (reservedMap[hh].student_mb_id == mb_id) {
      cls += 'mine';
    } else {
      cls += 'disabled';
    }

    html += `<div class="${cls}" data-time="${hh}">${label}</div>`;
  }

  $('#timeGrid').html(html);

  $('.time-cell').off('click').on('click', function() {
    const cell = $(this);
    const t = cell.data('time');

    if (cell.hasClass('available')) {
      selectedTime = t;
      openBookSheet();
    } else if (cell.hasClass('mine')) {
      if (confirm("해당 예약을 취소하시겠습니까?")) {
        const found = list.find(x => x.scheduled_dt.substring(11, 19) == t);
        ConsultAPI.remove(found.id).then(() => {
          alert("취소되었습니다.");
          markTimeStates();
          loadMyConsult(1, true);
        });
      }
    }
  });
}

/* ===============================
      예약 bottom sheet
=============================== */
function openBookSheet() {
  $('#bookDim').show();
  $('#bookSheet').show().css('bottom', '0');
}
function closeBookSheet() {
  $('#bookSheet').css('bottom', '-70%');
  setTimeout(() => $('#bookSheet').hide(), 250);
  $('#bookDim').hide();
}

/* ===============================
      예약하기
=============================== */
function reserveConsult() {
  if (!selectedTeacher || !selectedDate || !selectedTime) return;

  let scheduled_dt = selectedDate + ' ' + selectedTime;
  let requested_dt = getNowDateTime();

  ConsultAPI.add({
    student_mb_id: mb_id,
    teacher_mb_id: selectedTeacher,
    type: CONSULT_TYPE,
    requested_dt: requested_dt,
    scheduled_dt: scheduled_dt
  }).then(() => {
    alert("예약되었습니다.");
    closeBookSheet();
    markTimeStates();
    loadMyConsult(1, true);
  });
}

/* 현재시간 YYYY-MM-DD HH:MM:SS */
function getNowDateTime() {
  const d = new Date();
  const y = d.getFullYear();
  const m = String(d.getMonth() + 1).padStart(2, '0');
  const day = String(d.getDate()).padStart(2, '0');
  const hh = String(d.getHours()).padStart(2, '0');
  const mm = String(d.getMinutes()).padStart(2, '0');
  const ss = String(d.getSeconds()).padStart(2, '0');
  return `${y}-${m}-${day} ${hh}:${mm}:${ss}`;
}

/* ===============================
      내 상담 리스트
=============================== */
function loadMyConsult(p, reset) {
  if (reset) {
    $('#myList').html('');
    pageNum = 1;
  }

  ConsultAPI.byStudent(mb_id, p, 10).then(res => {
    const list = res.data || [];
    const total = res.total;

    list
      .filter(x => x.type == CONSULT_TYPE)
      .forEach(item => {
        $('#myList').append(`
          <div class="common-item">
            <div class="common-title">${item.scheduled_dt}</div>
            <div class="common-meta">${item.teacher_mb_id}</div>
          </div>
        `);
      });

    if ((p * 10) < total) $('#moreWrap').show();
    else $('#moreWrap').hide();
  });
}

function loadMore() {
  pageNum++;
  loadMyConsult(pageNum, false);
}
</script>

<?php include_once('../tail.php'); ?>
