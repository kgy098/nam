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
?>

<style>
  /* ================================
     멘토상담 전용 추가 스타일
     (학과상담과 동일, 변경 없음)
  ================================= */

  .consult-top-section {
    width: 90%;
    max-width: 420px;
    margin: 10px auto 0;
  }

  .consult-top-row {
    display: flex;
    gap: 10px;
    margin-bottom: 10px;
  }

  .consult-top-row .common-select-box {
    flex: 1;
  }

  .consult-info {
    width: 90%;
    max-width: 420px;
    margin: 6px auto 0;
    font-size: 13px;
    color: #ADABA6E0;
    line-height: 1.5em;
  }

  .consult-state-wrap {
    width: 90%;
    max-width: 420px;
    margin: 14px auto 6px;
    display: flex;
    gap: 16px;
  }

  .consult-state-row {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #D9D8D5E0;
  }

  .consult-state-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
  }

  .consult-dot-available {
    background: #3A3A3F;
  }

  .consult-dot-mine {
    background: #A17E36;
  }

  .consult-dot-disabled {
    background: #333;
  }

  .consult-time-grid {
    width: 90%;
    max-width: 420px;
    margin: 10px auto 0;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 8px;
  }

  .consult-time-slot {
    height: 40px;
    border-radius: 6px;
    background: #2b2c31;
    border: 1px solid #2b2c31;
    font-size: 13px;
    color: #D9D8D5E0;
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
  }

  .consult-time-slot.available {
    background: #3A3A3F;
  }

  .consult-time-slot.mine {
    background: #A17E36;
    color: #000;
    font-weight: 600;
  }

  .consult-time-slot.disabled {
    opacity: 0.25;
  }

  .consult-time-slot.selected {
    border: 1px solid #E4CA92;
  }

  .consult-my-section-title {
    width: 90%;
    max-width: 420px;
    margin: 20px auto 0;
    font-size: 15px;
    font-weight: 600;
    color: #ffffffe0;
  }

  .consult-cancel-btn {
    border: 1px solid #3A3A3F;
    background: #2a2a31;
    color: #D9D8D5E0;
    border-radius: 6px;
    padding: 6px 10px;
    font-size: 12px;
    flex-shrink: 0;
  }

  .consult-cancel-btn:active {
    opacity: 0.9;
  }

  .consult-sheet-dim {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0, 0, 0, 0.4);
    z-index: 900;
  }

  .consult-sheet-box {
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

  .consult-sheet-header {
    text-align: center;
    font-size: 16px;
    font-weight: 600;
    color: #E5C784;
    margin-bottom: 12px;
  }

  .consult-sheet-body {
    padding: 14px 0;
    text-align: center;
    font-size: 15px;
    color: #fff;
  }

  .consult-sheet-btn-wrap {
    margin-top: 18px;
    display: flex;
    gap: 12px;
  }

  .consult-sheet-btn {
    flex: 1;
    height: 46px;
    border-radius: 8px;
    background: #2A2A31;
    border: 1px solid #3A3A3F;
    font-size: 15px;
    color: #D9D8D5E0;
  }

  .consult-sheet-btn.confirm {
    background: #A17E36;
    border: none;
    color: #000;
    font-weight: 600;
  }
</style>

<!-- 상단 선택 영역 -->
<div class="consult-top-section">
  <div class="common-form-row first-row consult-top-row">

    <div class="common-select-box">
      <select id="selTeacher" class="common-select">
        <option value="">선생님 선택</option>
      </select>
    </div>

    <div class="common-select-box">
      <select id="selDate" class="common-select">
        <option value="">날짜 선택</option>
      </select>
    </div>
  </div>
</div>

<div class="consult-info">
  상담을 원하는 선생님과 날짜를 선택한 뒤, 상담 가능 시간을 탭하여 예약하세요.
</div>

<!-- 상태 안내 -->
<div class="consult-state-wrap">
  <div class="consult-state-row">
    <div class="consult-state-dot consult-dot-available"></div>상담가능
  </div>
  <div class="consult-state-row">
    <div class="consult-state-dot consult-dot-mine"></div>내상담
  </div>
  <div class="consult-state-row">
    <div class="consult-state-dot consult-dot-disabled"></div>상담불가
  </div>
</div>

<div id="timeGrid" class="consult-time-grid"></div>

<div class="consult-my-section-title">내 상담 내역</div>

<div class="common-list-container" id="myConsultList"></div>

<!-- bottom sheet -->
<div class="consult-sheet-dim" id="consultBookDim" onclick="closeConsultBookSheet();"></div>
<div class="consult-sheet-box" id="consultBookSheet">
  <div class="consult-sheet-header">예약 확인</div>
  <div class="consult-sheet-body" id="consultBookText">
    선택한 시간으로 상담을 예약하시겠습니까?
  </div>
  <div class="consult-sheet-btn-wrap">
    <button class="consult-sheet-btn" onclick="closeConsultBookSheet();">취소</button>
    <button class="consult-sheet-btn confirm" onclick="reserveConsult();">예약하기</button>
  </div>
</div>

<script src="<?= G5_API_URL ?>/api_consult.js"></script>

<script>
  var mb_id = "<?= $mb_id ?>";

  var selectedTeacher = '';
  var selectedDate = '';
  var pendingScheduledDt = '';
  var teacherMap = {};
  var CONSULT_TYPE = "멘토상담";   // ★ 변경된 부분

  $(document).ready(function () {
    if (!mb_id) return;
    initConsultScreen();
  });

  function initConsultScreen() {
    ConsultAPI.teacherList().then(function (res) {
      var list = res.data || [];
      teacherMap = {};
      var html = '<option value="">선생님 선택</option>';

      list.forEach(function (t) {
        teacherMap[t.mb_id] = t.mb_name;
        html += '<option value="' + t.mb_id + '">' + t.mb_name + '</option>';
      });

      $('#selTeacher').html(html);

      if (list.length > 0) {
        selectedTeacher = list[0].mb_id;
        $('#selTeacher').val(selectedTeacher);
      }

      return ConsultAPI.dateList();

    }).then(function (res) {
      var dates = res.data || [];
      var html = '<option value="">날짜 선택</option>';

      dates.forEach(function (d) {
        var parts = d.split('-');
        var label = parts[1] + '/' + parts[2];
        html += '<option value="' + d + '">' + label + '</option>';
      });

      $('#selDate').html(html);

      if (dates.length > 0) {
        selectedDate = dates[0];
        $('#selDate').val(selectedDate);
      }

      loadTimeSlots();
      loadMyConsults();

    }).fail(function () {
      alert('멘토상담 정보를 불러오는 중 오류가 발생했습니다.');
    });

    $('#selTeacher').on('change', function () {
      selectedTeacher = $(this).val() || '';
      loadTimeSlots();
      loadMyConsults();
    });

    $('#selDate').on('change', function () {
      selectedDate = $(this).val() || '';
      loadTimeSlots();
    });
  }

  function loadTimeSlots() {
    if (!selectedTeacher || !selectedDate) {
      $('#timeGrid').html('');
      return;
    }

    ConsultAPI.times({
      student_mb_id: mb_id,
      teacher_mb_id: selectedTeacher,
      target_date: selectedDate
    }).then(function (res) {
      renderTimeGrid(res.data || []);
    });
  }

  function renderTimeGrid(list) {
    var html = '';

    list.forEach(function (slot) {
      var cls = 'consult-time-slot';

      if (slot.status === '상담가능') cls += ' available';
      else if (slot.status === '내상담') cls += ' mine';
      else cls += ' disabled';

      html += '<div class="' + cls + '" ' +
        'data-dt="' + (slot.scheduled_dt || '') + '" ' +
        'data-status="' + (slot.status || '') + '">' +
        slot.time +
        '</div>';
    });

    $('#timeGrid').html(html);

    $('#timeGrid .consult-time-slot').each(function () {
      var $slot = $(this);
      var status = $slot.data('status');
      var dt = $slot.data('dt');

      $slot.off('click').on('click', function () {
        if (status === '상담가능') {
          pendingScheduledDt = dt;
          openConsultBookSheet();
        } else if (status === '내상담') {
          alert('내 상담은 아래 "내 상담 내역"에서 취소할 수 있습니다.');
        }
      });
    });
  }

  function openConsultBookSheet() {
    if (!pendingScheduledDt || !selectedTeacher) return;

    var msg = 
      '선택한 시간으로 멘토상담을 예약하시겠습니까?<br>' +
      '<span style="font-size:13px;color:#ADABA6E0;">' +
      selectedDate + ' ' + pendingScheduledDt.substring(11, 16) +
      ' / ' + (teacherMap[selectedTeacher] || selectedTeacher) +
      '</span>';

    $('#consultBookText').html(msg);

    $('#consultBookDim').show();
    $('#consultBookSheet').show().css('bottom', '0');
  }

  function closeConsultBookSheet() {
    $('#consultBookSheet').css('bottom', '-70%');
    setTimeout(function () {
      $('#consultBookSheet').hide();
    }, 250);
    $('#consultBookDim').hide();
  }

  function reserveConsult() {
    if (!pendingScheduledDt || !selectedTeacher) return;

    ConsultAPI.reserve({
      student_mb_id: mb_id,
      teacher_mb_id: selectedTeacher,
      scheduled_dt: pendingScheduledDt,
      type: CONSULT_TYPE    // ★ type = 멘토상담
    }).then(function () {
      alert('상담이 예약되었습니다.');
      closeConsultBookSheet();
      loadTimeSlots();
      loadMyConsults();
    });
  }

  function loadMyConsults() {
    ConsultAPI.myList(mb_id).then(function (res) {
      var list = res.data || [];
      renderMyConsultList(list);
    });
  }

  function renderMyConsultList(list) {
    var html = '';

    if (!list.length) {
      html = '<div style="font-size:13px;color:#ADABA6E0;">예약된 상담이 없습니다.</div>';
      $('#myConsultList').html(html);
      return;
    }

    list
      .filter(function (row) {
        return row.type === CONSULT_TYPE;   // ★ 멘토상담만
      })
      .forEach(function (row) {

        var id = row.id;
        var dt = row.scheduled_dt || '';
        var datePart = dt.substring(0, 10);
        var timePart = dt.substring(11, 16);
        var teacherName = teacherMap[row.teacher_mb_id] || row.teacher_mb_id;
        var status = row.status || '';

        html +=
          '<div class="common-item" data-id="' + id + '">' +
          '  <div class="common-item-row">' +
          '    <div class="common-info">' +
          '      <div class="common-title">' +
          datePart + ' ' + timePart + ' · 멘토상담' +
          '      </div>' +
          '      <div class="common-meta">' +
          teacherName + ' · 상태: ' + status +
          '      </div>' +
          '    </div>' +
          '    <button type="button" class="consult-cancel-btn">취소</button>' +
          '  </div>' +
          '</div>';
      });

    $('#myConsultList').html(html);

    $('#myConsultList .consult-cancel-btn').each(function () {
      var $btn = $(this);
      var $item = $btn.closest('.common-item');
      var id = $item.data('id');

      $btn.off('click').on('click', function () {
        if (!confirm('해당 상담을 취소하시겠습니까?')) return;

        ConsultAPI.cancel(id, mb_id).then(function () {
          alert('상담이 취소되었습니다.');
          loadTimeSlots();
          loadMyConsults();
        });
      });
    });
  }
</script>

<?php include_once('../tail.php'); ?>
