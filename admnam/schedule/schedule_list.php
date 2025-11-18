<?php
$sub_menu = "020100";
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '학사일정';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_schedule.js"></script>

<style>
  .sch_wrap {
    display: flex;
    gap: 30px;
    align-items: flex-start;
  }

  .calendar_wrap {
    flex: 1;
  }

  .calendar_header {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 20px;
    margin-bottom: 10px;
  }

  .calendar_header button {
    padding: 4px 10px;
  }

  .calendar_table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
  }

  .calendar_table th,
  .calendar_table td {
    border: 1px solid #ddd;
    height: 70px;
    vertical-align: top;
    padding: 3px;
    font-size: 12px;
  }

  .calendar_table th {
    background: #f5f5f5;
    text-align: center;
  }

  .calendar_table td {
    cursor: pointer;
  }

  .calendar_table td.disabled {
    background: #fafafa;
    color: #ccc;
    cursor: default;
  }

  .calendar_table td .day-num {
    font-weight: bold;
    margin-bottom: 3px;
  }

  .calendar_table td.selected {
    outline: 2px solid #444;
  }

  .calendar_table td .event {
    font-size: 11px;
    margin-top: 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .calendar_table td.sun .day-num {
    color: #c00;
  }

  .calendar_table td.sat .day-num {
    color: #0066cc;
  }

  .desc_wrap {
    width: 260px;
  }

  .desc_wrap table {
    width: 100%;
    border-collapse: collapse;
  }

  .desc_wrap th,
  .desc_wrap td {
    border: 1px solid #ddd;
    padding: 4px;
    font-size: 12px;
  }

  .desc_wrap th {
    background: #f5f5f5;
    text-align: center;
  }

  .desc_wrap td.no {
    width: 35px;
    text-align: center;
  }

  .schedule_form {
    margin-top: 15px;
    text-align: center;
  }

  .schedule_form input[type="text"] {
    width: 260px;
  }

  .event-item {
    position: relative;
    padding-right: 14px;
    /* X 버튼 공간 */
    font-size: 11px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
  }

  .event-del {
    position: absolute;
    right: 0;
    top: 0;
    font-size: 11px;
    cursor: pointer;
    opacity: 0.5;
  }

  .event-del:hover {
    opacity: 1;
    color: #ff5252;
  }
</style>

<div class="sch_wrap">

  <div class="calendar_wrap">
    <div class="calendar_header">
      <button type="button" id="btnPrevMonth">&lt;</button>
      <div>
        <span id="calYear"></span>년
        <span id="calMonth"></span>월
      </div>
      <button type="button" id="btnNextMonth">&gt;</button>
    </div>

    <table class="calendar_table" id="calendarTable">
      <thead>
        <tr>
          <th>일</th>
          <th>월</th>
          <th>화</th>
          <th>수</th>
          <th>목</th>
          <th>금</th>
          <th>토</th>
        </tr>
      </thead>
      <tbody id="calendarBody">
      </tbody>
    </table>

    <div class="schedule_form">
      <input type="text" id="scheduleTitle" class="frm_input" placeholder="날짜를 선택 후 학사일정을 입력하세요.">
      <button type="button" class="btn btn_02" id="btnAddSchedule">등록</button>
    </div>
  </div>

</div>

<script>
  var currentYear, currentMonth; // month: 1~12
  var selectedDate = ''; // YYYY-MM-DD
  var scheduleList = []; // 전체 일정 캐시

  $(function() {
    var today = new Date();
    currentYear = today.getFullYear();
    currentMonth = today.getMonth() + 1;

    $('#btnPrevMonth').on('click', function() {
      currentMonth--;
      if (currentMonth < 1) {
        currentMonth = 12;
        currentYear--;
      }
      renderCalendar();
    });

    $('#btnNextMonth').on('click', function() {
      currentMonth++;
      if (currentMonth > 12) {
        currentMonth = 1;
        currentYear++;
      }
      renderCalendar();
    });

    $('#btnAddSchedule').on('click', function() {
      addSchedule();
    });
    $('#scheduleTitle').on('keydown', function(e) {
      if (e.key === 'Enter') {
        e.preventDefault(); // 폼 제출/줄바꿈 방지
        addSchedule(); // 동일한 등록 함수 호출
      }
    });

    renderCalendar();

    setEvent();
  });

  function setEvent() {
    $(document).on('click', '.event-del', function(e) {
      e.stopPropagation(); // 날짜 클릭과 충돌 방지됨

      var id = $(this).closest('.event-item').data('id');

      if (!id) return;

      if (!confirm('이 일정을 삭제하시겠습니까?')) return;

      ScheduleAPI.remove(id).then(function(res) {
        if (res.result === 'SUCCESS') {
          alert('삭제되었습니다.');

          // 리스트 다시 불러오고 다시 렌더링
          location.reload();
        } else {
          alert('삭제 실패');
        }
      });
    });

  }

  function renderCalendar() {
    $('#calYear').text(currentYear);
    $('#calMonth').text(currentMonth);

    var firstDate = new Date(currentYear, currentMonth - 1, 1);
    var firstDay = firstDate.getDay();
    var lastDate = new Date(currentYear, currentMonth, 0).getDate();

    var tbody = $('#calendarBody');
    tbody.empty();

    var day = 1;
    for (var row = 0; row < 6; row++) {
      var tr = $('<tr></tr>');
      for (var col = 0; col < 7; col++) {
        var td = $('<td></td>');

        if (row === 0 && col < firstDay || day > lastDate) {
          td.addClass('disabled');
          td.html('&nbsp;');
        } else {
          var yyyy = String(currentYear);
          var mm = (currentMonth < 10 ? '0' : '') + currentMonth;
          var dd = (day < 10 ? '0' : '') + day;
          var dateStr = yyyy + '-' + mm + '-' + dd;

          td.attr('data-date', dateStr);
          td.addClass('day-cell');
          if (col === 0) td.addClass('sun');
          if (col === 6) td.addClass('sat');

          td.append('<div class="day-num">' + day + '</div>');
          td.append('<div class="event-wrap"></div>');

          day++;
        }

        tr.append(td);
      }
      tbody.append(tr);
    }

    $('.day-cell').off('click').on('click', function() {
      $('.day-cell').removeClass('selected');
      $(this).addClass('selected');
      selectedDate = $(this).data('date');
      renderDescription(selectedDate);
    });

    loadScheduleForMonth();
  }

  function loadScheduleForMonth() {
    ScheduleAPI.list(1, 500).then(function(res) {
      scheduleList = res.data || [];
      fillCalendarEvents();
      if (selectedDate) renderDescription(selectedDate);
    });
  }

  function fillCalendarEvents() {
    $('.day-cell .event-wrap').empty();

    var ym = currentYear + '-' + (currentMonth < 10 ? '0' : '') + currentMonth;

    scheduleList.forEach(function(row) {

      var sd = row.start_date ? row.start_date.substring(0, 10) : '';
      if (!sd.startsWith(ym)) return;

      var $cell = $('.day-cell[data-date="' + sd + '"]');
      if (!$cell.length) return;

      var $wrap = $cell.find('.event-wrap');

      var item = $(`
            <div class="event-item" data-id="${row.id}">
                ${row.title}
                <span class="event-del">×</span>
            </div>
        `);

      $wrap.append(item);
    });
  }

  function renderDescription(dateStr) {
    var $tbody = $('#descBody');
    $tbody.empty();

    $tbody.append(
      '<tr><td class="no">*</td><td>' +
      dateStr +
      ' 일정입니다.</td></tr>'
    );

    var list = scheduleList.filter(function(row) {
      var sd = row.start_date ? row.start_date.substring(0, 10) : '';
      var ed = row.end_date ? row.end_date.substring(0, 10) : sd;

      return (sd <= dateStr && dateStr <= ed);
    });

    if (!list.length) {
      for (var i = 1; i <= 12; i++) {
        $tbody.append('<tr><td class="no">' + i + '</td><td>&nbsp;</td></tr>');
      }
      return;
    }

    for (var i = 0; i < 12; i++) {
      var txt = list[i] ? (list[i].title || '') : '';
      $tbody.append(
        '<tr><td class="no">' + (i + 1) + '</td><td>' + (txt || '&nbsp;') + '</td></tr>'
      );
    }
  }

  function addSchedule() {
    if (!selectedDate) {
      alert('날짜를 먼저 선택하세요.');
      return;
    }
    var title = $.trim($('#scheduleTitle').val());
    if (!title) {
      alert('학사일정을 입력하세요.');
      $('#scheduleTitle').focus();
      return;
    }

    ScheduleAPI.add({
      title: title,
      start_date: selectedDate
    }).then(function() {
      $('#scheduleTitle').val('');
      loadScheduleForMonth();
      renderDescription(selectedDate);
    });
  }
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>