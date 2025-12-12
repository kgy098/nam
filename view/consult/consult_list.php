<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "학과상담";
include_once('../head.php');

// 로그인 사용자
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') {
  alert("로그인이 필요합니다.");
}
?>

<div class="wrap">
  <!-- ================================
     상단 선택 영역 (선생님 / 날짜)
================================ -->
  <div class="consult-top-section">
    <div class="common-form-row first-row consult-top-row">
      <!-- 선생님 선택 -->
      <div class="common-select-box">
        <select id="selTeacher" class="common-select">
          <option value="">선생님 선택</option>
        </select>
      </div>

      <!-- 날짜 선택 -->
      <div class="common-select-box">
        <select id="selDate" class="common-select">
          <option value="">날짜 선택</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 안내문 -->
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

  <!-- 시간표 그리드 -->
  <div id="timeGrid" class="consult-time-grid"></div>

  <!-- 내 상담 리스트 타이틀 -->
  <div class="common-section-title">
    내 상담 내역
  </div>

  <!-- 내 상담 리스트 -->
  <div class="common-list-container" id="myConsultList"></div>

  <!-- 예약 확인 bottom sheet -->
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
</div>

<script src="<?= G5_API_URL ?>/api_consult.js"></script>

<script>
  var mb_id = "<?= $mb_id ?>";

  var selectedTeacher = '';
  var selectedDate = '';
  var pendingScheduledDt = '';
  var teacherMap = {}; // mb_id -> mb_name

  $(document).ready(function() {
    if (!mb_id) return;

    initConsultScreen();
  });

  function initConsultScreen() {
    // 1) 선생님 목록 로딩
    ConsultAPI.teacherList().then(function(res) {
      var list = res.data || [];
      teacherMap = {};
      var html = '<option value="">선생님 선택</option>';

      list.forEach(function(t) {
        teacherMap[t.mb_id] = t.mb_name;
        html += '<option value="' + t.mb_id + '">' + t.mb_name + '</option>';
      });

      $('#selTeacher').html(html);

      if (list.length > 0) {
        selectedTeacher = list[0].mb_id;
        $('#selTeacher').val(selectedTeacher);
      }

      // 2) 날짜 리스트 로딩
      return ConsultAPI.dateList();

    }).then(function(res) {
      var dates = res.data || [];
      var html = '<option value="">날짜 선택</option>';

      dates.forEach(function(d) {
        // d: YYYY-MM-DD → MM/DD 표시
        var parts = d.split('-');
        var label = parts[1] + '/' + parts[2];
        html += '<option value="' + d + '">' + label + '</option>';
      });

      $('#selDate').html(html);

      if (dates.length > 0) {
        selectedDate = dates[0];
        $('#selDate').val(selectedDate);
      }

      // 3) 첫 로딩 시 시간표 + 내 상담 로딩
      loadTimeSlots();
      loadMyConsults();

    }).fail(function(err) {
      console.log(err);
      alert('학과상담 정보를 불러오는 중 오류가 발생했습니다.');
    });

    // 이벤트: 선생님 변경
    $('#selTeacher').on('change', function() {
      selectedTeacher = $(this).val() || '';
      loadTimeSlots();
      loadMyConsults();
    });

    // 이벤트: 날짜 변경
    $('#selDate').on('change', function() {
      selectedDate = $(this).val() || '';
      loadTimeSlots();
    });
  }

  /* ============================================
     시간표 로딩
  ============================================ */
  function loadTimeSlots() {
    if (!selectedTeacher || !selectedDate) {
      $('#timeGrid').html('');
      return;
    }

    ConsultAPI.times({
      student_mb_id: mb_id,
      teacher_mb_id: selectedTeacher,
      target_date: selectedDate,
      consult_type: '학과상담'
    }).then(function(res) {
      var list = res.data || [];
      renderTimeGrid(list);
    }).fail(function(err) {
      console.log(err);
      $('#timeGrid').html('');
      alert('시간표를 불러오는 중 오류가 발생했습니다.');
    });
  }

  function renderTimeGrid(list) {
    var html = '';

    list.forEach(function(slot) {
      var cls = 'consult-time-slot';
      if (slot.status === '상담가능') {
        cls += ' available';
      } else if (slot.status === '내상담' && slot.consult_type === '학과상담') {
        cls += ' mine';
      } else {
        cls += ' disabled';
      }

      html += '<div class="' + cls + '" ' +
        'data-dt="' + (slot.scheduled_dt || '') + '" ' +
        'data-status="' + (slot.status || '') + '" ' +
        'data-id="' + (slot.consult_id || '') +
        '">' +
        slot.time +
        '</div>';
    });

    $('#timeGrid').html(html);

    // 클릭 이벤트
    $('#timeGrid .consult-time-slot').each(function() {
      var $slot = $(this);
      var status = $slot.data('status');
      var dt = $slot.data('dt');

      $slot.off('click').on('click', function() {
        if (status === '상담가능') {
          pendingScheduledDt = dt;
          openConsultBookSheet();
        } else if (status === '내상담') {
          calcel($slot.data('id'));
        } else {
          // 상담불가
          return;
        }
      });
    });
  }

  /* ============================================
     예약 bottom sheet
  ============================================ */
  function openConsultBookSheet() {
    if (!pendingScheduledDt || !selectedTeacher) return;

    var msg = '선택한 시간으로 학과상담을 예약하시겠습니까?<br>' +
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
    setTimeout(function() {
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
      consult_type: '학과상담'
    }).then(function() {
      alert('상담이 예약되었습니다.');
      closeConsultBookSheet();
      loadTimeSlots();
      loadMyConsults();
    }).fail(function(err) {
      console.log(err);
      alert((err && err.data) || '예약 중 오류가 발생했습니다.');
    });
  }

  /* ============================================
     내 상담 리스트
  ============================================ */
  function loadMyConsults() {
    ConsultAPI.myList(mb_id, '학과상담').then(function(res) {
      var list = res.data || [];
      renderMyConsultList(list);
    }).fail(function(err) {
      console.log(err);
      $('#myConsultList').html('');
    });
  }

  function renderMyConsultList(list) {
    var html = '';

    if (!list.length) {
      html = '<div style="font-size:13px;color:#ADABA6E0;">' +
        '예약된 상담이 없습니다.' +
        '</div>';
      $('#myConsultList').html(html);
      return;
    }

    list.forEach(function(row) {
      var id = row.id;
      var dt = row.scheduled_dt || '';
      var datePart = '';
      var timePart = '';
      if (dt.length >= 16) {
        datePart = dt.substring(0, 10);
        timePart = dt.substring(11, 16);
      }

      var teacherName = teacherMap[row.teacher_mb_id] || row.teacher_mb_id;
      var type = row.type || '학과상담';
      var status = row.status || '';

      html +=
        `
        <div class="common-item" data-id="${id}">
          <div class="common-item-row">
            <div class="common-info">
              <div class="common-title">
                ${datePart ? `${datePart} ${timePart}` : ''} · ${type}
              </div>
              <div class="common-meta">${teacherName} · 상태: ${status}
              </div>
            </div>
            <button type="button" class="consult-cancel-btn">취소</button>
          </div>
        </div>
        `;
    });

    $('#myConsultList').html(html);

    // 취소 버튼 이벤트
    $('#myConsultList .consult-cancel-btn').each(function() {
      var $btn = $(this);
      var $item = $btn.closest('.common-item');
      var id = $item.data('id');

      $btn.off('click').on('click', function() {
        if (!confirm('해당 상담을 취소하시겠습니까?')) return;

        calcel(id);
      });
    });
  }

  function calcel(id) {
    ConsultAPI.cancel(id).then(function() {
      alert('상담이 취소되었습니다.');
      loadTimeSlots();
      loadMyConsults();
    }).fail(function(err) {
      console.log(err);
      alert((err && err.data) || '취소 중 오류가 발생했습니다.');
    });
  }
</script>

<?php include_once('../tail.php'); ?>