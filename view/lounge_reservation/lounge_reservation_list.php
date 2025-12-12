<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "스터디 라운지 예약";
include_once('../head.php');

// 로그인 사용자
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') {
  alert("로그인이 필요합니다.");
}
?>

<style>
  /* ================================
   라운지 예약 전용 UI 추가 스타일
   nam.css 스타일을 그대로 유지하면서 필요한 것만 추가
================================ */

  /* 첫 번째 줄 3셀 (라운지/날짜/시간) */
  .resv-top-row {
    width: 90%;
    max-width: 420px;
    margin: 12px auto 0;
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 10px;
  }

  /* 안내문 */
  .resv-info {
    width: 90%;
    max-width: 420px;
    margin: 10px auto 0;
    font-size: 13px;
    color: #ADABA6E0;
    line-height: 1.5em;
  }

  /* 좌석 상태 라벨 */
  .seat-state-wrap {
    width: 90%;
    max-width: 420px;
    margin: 14px auto 6px;
    display: flex;
    gap: 16px;
  }

  .state-dot {
    width: 10px;
    height: 10px;
    border-radius: 50%;
    margin-right: 6px;
  }

  .state-row {
    display: flex;
    align-items: center;
    font-size: 13px;
    color: #D9D8D5E0;
  }

  /* 색상 정의 */
  .dot-available {
    background: #3A3A3F;
  }

  .dot-mine {
    background: #A17E36;
  }

  .dot-disabled {
    background: #333;
  }

  /* 좌석 그리드 */
  .lounge-seat-grid {
    width: 100%;
    padding: 0 16px;
    /* 좌우 여백 */
    box-sizing: border-box;
    display: flex;
    flex-direction: column;
    gap: 8px;
  }

  .lounge-seat-row {
    width: 100%;
    display: flex;
    gap: 8px;
  }

  .lounge-seat {
    flex: 1;
    /* 칸 개수에 따라 width 자동 분배 */
    aspect-ratio: 1 / 1;
    /* 정사각형 */
    background: #2b2c31;
    border-radius: 6px;
    display: flex;
    justify-content: center;
    align-items: center;
    color: #D9D8D5;
    font-size: 14px;
    border: 1px solid #2b2c31;
  }

  .lounge-seat.available {
    background: #3A3A3F;
  }

  .lounge-seat.mine {
    background: #A17E36;
    color: #fff;
    font-weight: 600;
  }

  .lounge-seat.disabled {
    opacity: 0.25;
  }

  .lounge-seat.selected {
    border: 1px solid #E4CA92;
  }

  /* 복도(빈칸) */
  .lounge-seat-aisle {
    flex: 1;
    aspect-ratio: 1 / 1;
    background: transparent !important;
  }

  /* bottom sheet (공통 스타일 기반) */
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

  .sheet-item {
    padding: 12px 0;
    text-align: center;
    font-size: 15px;
    color: #fff;
    border-bottom: 1px solid #2A2A2C;
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

<div class="wrap">
  <!-- ======================================
     첫 번째 줄 (라운지 / 날짜 / 시간 선택)
====================================== -->
  <div class="resv-top-row">

    <!-- 라운지 -->
    <div class="common-select-box">
      <select id="selLounge" class="common-select"></select>
    </div>

    <!-- 날짜 -->
    <div class="common-select-box">
      <select id="selDate" class="common-select"></select>
    </div>

    <!-- 시간 -->
    <div class="common-select-box">
      <select id="selTime" class="common-select">
      </select>
    </div>
  </div>

  <!-- ======================================
     안내문
====================================== -->
  <div class="resv-info">
    예약하고 싶은 자리를 탭하여 예약하세요.
  </div>

  <!-- ======================================
     좌석 상태 안내
====================================== -->
  <div class="seat-state-wrap">
    <div class="state-row">
      <div class="state-dot dot-available"></div>예약가능
    </div>
    <div class="state-row">
      <div class="state-dot dot-mine"></div>내예약
    </div>
    <div class="state-row">
      <div class="state-dot dot-disabled"></div>예약불가
    </div>
  </div>

  <!-- ======================================
     좌석 배치 (cell_no 기반)
====================================== -->
  <div id="seatGrid" class="lounge-seat-grid"></div>

  <div class="common-section-title">내 예약현황</div>

  <!-- ======================================
     내 예약 리스트
====================================== -->
  <div class="common-list-container" id="myResList"></div>
  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>

  <!-- ======================================
     예약 확인 bottom sheet
====================================== -->
  <div class="sheet-dim" id="bookDim" onclick="closeBookSheet();"></div>
  <div class="sheet-box" id="bookSheet">
    <div class="sheet-header">예약 확인</div>
    <div style="padding:14px 0; text-align:center; font-size:15px; color:#fff;">
      이 좌석을 예약하시겠습니까?
    </div>
    <div class="sheet-btn-wrap">
      <button class="sheet-btn" onclick="closeBookSheet();">취소</button>
      <button class="sheet-btn confirm" onclick="reserveSeat();">예약하기</button>
    </div>
  </div>

</div>

<!-- ======================================
     JS (API 연동 + UI 처리)
====================================== -->
<script src="<?= G5_API_URL ?>/api_lounge.js"></script>
<script src="<?= G5_API_URL ?>/api_lounge_seat.js"></script>
<script src="<?= G5_API_URL ?>/api_lounge_reservation.js"></script>

<script>
  var mb_id = "<?= $mb_id ?>";
  var selectedLounge = null;
  var selectedDate = null;
  var selectedTime = null;
  var selectedSeatId = null;

  /* ============================================
     초기 로딩
  ============================================ */
  $(document).ready(function() {
    selectedLounge = $(this).val();

    loadToday();
    loadDateOptions();
    updateTimeList();


    loadLounges();

    $('#selLounge').on('change', function() {
      selectedLounge = $(this).val();
      loadSeatGrid(); // → 좌석 배치 다시 생성
      loadMyReservations(1, true); // 내 예약 다시 로딩
    });

    $('#selDate').on('change', function() {
      selectedDate = $(this).val();
      markSeatStates();
      loadSeatGrid();
      loadMyReservations(1, true);
    });

    $('#selTime').on('change', function() {
      selectedTime = $(this).val();
      markSeatStates(); // → 해당 시간 예약 현황 반영
      loadSeatGrid();
      loadMyReservations(1, true);
    });

  });

  /* ============================================
     1) 라운지 로딩
  ============================================ */
  function loadLounges() {
    loungeAPI.list(1, 50).then(res => {
      let list = res.data || [];
      let html = '';

      // console.log("LOUNGE: " + list);
      list.forEach(function(item) {
        // A 라운지 자동선택
        if (item.name === 'A') selectedLounge = item.id;
        html += `<option value="${item.id}">${item.name}</option>`;
      });

      $('#selLounge').html(html);

      if (!selectedLounge && list.length > 0) {
        selectedLounge = list[0].id;
      }

      $('#selLounge').val(selectedLounge);

      loadSeatGrid();
      loadMyReservations(1);

    });
  }


  /* ============================================
     좌석 그리드 로딩 (cell_no 기반)
  ============================================ */
  function loadSeatGrid() {
    if (!selectedLounge || !selectedDate || !selectedTime) return;

    loungeSeatAPI.byLounge(selectedLounge).then(function(res) {
      const list = res.data || [];
      if (!list.length) {
        $('#seatGrid').html('');
        return;
      }

      // -------------------------------
      // 1) cell_no → row, col 계산
      // -------------------------------
      list.forEach(seat => {
        const cell = Number(seat.cell_no);
        seat.row = Math.floor((cell - 1) / 30) + 1; // 1~30
        seat.col = ((cell - 1) % 30) + 1; // 1~30
      });

      // -------------------------------
      // 2) row/col 최소/최대 계산
      // -------------------------------
      let minRow = Infinity,
        maxRow = -Infinity;
      let minCol = Infinity,
        maxCol = -Infinity;

      list.forEach(seat => {
        if (seat.row < minRow) minRow = seat.row;
        if (seat.row > maxRow) maxRow = seat.row;
        if (seat.col < minCol) minCol = seat.col;
        if (seat.col > maxCol) maxCol = seat.col;
      });

      const rowCount = maxRow - minRow + 1;
      const colCount = maxCol - minCol + 1;

      // -------------------------------
      // 3) 2D grid 준비 (null 로 초기화)
      // -------------------------------
      const grid = [];
      for (let r = 0; r < rowCount; r++) {
        grid[r] = Array(colCount).fill(null);
      }

      // -------------------------------
      // 4) grid 안에 좌석 배치
      // -------------------------------
      list.forEach(seat => {
        const r = seat.row - minRow;
        const c = seat.col - minCol;
        grid[r][c] = seat;
      });

      // -------------------------------
      // 5) HTML 생성
      // -------------------------------
      let html = "";

      for (let r = 0; r < rowCount; r++) {
        let rowHtml = "";

        for (let c = 0; c < colCount; c++) {
          const seat = grid[r][c];

          if (seat) {
            rowHtml += `
            <div class="lounge-seat" data-seat="${seat.id}">
              ${seat.seat_no}
            </div>
          `;
          } else {
            rowHtml += `<div class="lounge-seat-aisle"></div>`;
          }
        }

        html += `<div class="lounge-seat-row">${rowHtml}</div>`;
      }

      $('#seatGrid').html(html);

      // 기존 상태 반영 호출
      markSeatStates();
    });
  }


  /* ============================================
     해당 날짜/시간대 좌석 상태 표시
  ============================================ */

  function markSeatStates() {
    if (!selectedLounge || !selectedDate || !selectedTime) return;

    LoungeReservationAPI.byDate(selectedDate, {
        lounge_id: selectedLounge
      })
      .then(res => {
        applySeatState(res.data || []);
      })
      .fail(() => {
        // API 실패 → 예약 데이터 없음으로 처리
        applySeatState([]);
      });
  }


  function applySeatState(list) {

    // 동일 시간대만 필터
    list = list.filter(x => x.start_time === selectedTime);

    $('.lounge-seat').each(function() {
      const seat = $(this);
      const seat_id = seat.data('seat');

      // 기존 상태 모두 제거
      seat.removeClass('available mine disabled');

      const found = list.find(x => x.seat_id == seat_id);

      if (!found) {
        seat.addClass('available');
      } else if (found.mb_id === mb_id) {
        seat.addClass('mine');
      } else {
        seat.addClass('disabled');
      }

      // 클릭 이벤트
      seat.off('click').on('click', function() {
        if (seat.hasClass('available')) {
          selectedSeatId = seat_id;
          openBookSheet();
        } else if (seat.hasClass('mine')) {
          if (confirm("예약을 취소하시겠습니까?")) {
            LoungeReservationAPI.remove(found.id).then(() => {
              alert("취소되었습니다.");
              loadSeatGrid();
              loadMyReservations(1, true);
            });
          }
        }
      });
    });
  }

  /* ============================================
     예약 bottom sheet
  ============================================ */
  function openBookSheet() {
    $('#bookDim').show();
    $('#bookSheet').show().css('bottom', '0');
  }

  function closeBookSheet() {
    $('#bookSheet').css('bottom', '-70%');
    setTimeout(() => {
      $('#bookSheet').hide();
    }, 250);
    $('#bookDim').hide();
  }

  // 좌석예약
  function reserveSeat() {
    if (!selectedSeatId) return;

    // end_time = start +1h
    let h = parseInt(selectedTime.substring(0, 2), 10);
    let endH = String(h + 1).padStart(2, '0') + ":00:00";

    LoungeReservationAPI.add({
      mb_id: mb_id,
      lounge_id: selectedLounge,
      seat_id: selectedSeatId,
      reserved_date: selectedDate,
      start_time: selectedTime,
      end_time: endH
    }).then(res => {
      alert("예약 완료!");
      closeBookSheet();
      loadSeatGrid();
      loadMyReservations(1, true);
    }).fail(err => {
      console.log(err);
      alert(err.data || "예약 실패");
    });
  }

  /* ============================================
     내 예약 리스트
  ============================================ */
  let pageNum = 1;

  function loadMyReservations(p, reset) {
    if (reset) {
      $('#myResList').html('');
      pageNum = 1;
    }

    LoungeReservationAPI.byStudent(mb_id, p, 10).then(res => {
      const list = res.data || [];
      const total = res.data.total;

      list.forEach(item => {
        $('#myResList').append(`
          <div class="common-item">
            <div class="common-item-row">
              <div class="common-info">
                <div class="common-title">${item.reserved_date} ${item.start_time.substring(0,5)} 
                  <span class="common-meta">${item.l_name} / ${item.seat_no}번 좌석</span>
                </div>
              </div>
            </div>
          </div>
        `);
      });

      if ((p * 10) < total) {
        $('#moreWrap').show();
      } else {
        $('#moreWrap').hide();
      }
    });
  }

  /* 날짜 select 채우기(오늘~7일) */
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
    $('#selDate').html(html);
    $('#selDate').val(selectedDate);
  }

  function updateTimeList() {
    let now = new Date();
    let nowH = now.getHours();

    let html = '<option value="">시간선택</option>';

    for (let h = 0; h < 24; h++) {
      if (h <= nowH) continue; // 현재 시간 이전은 제외

      let label = formatKoreanTime(h);
      let hh = String(h).padStart(2, '0');

      html += `<option value="${hh}:00:00">${label}</option>`;
    }

    $('#selTime').html(html);

    // 기본 선택 - 가장 가까운 시간
    const first = $('#selTime option').eq(1);
    if (first.length > 0) {
      $('#selTime').val(first.val());
      selectedTime = first.val();
    }
  }

  /* ============================================
     2) 날짜 today
  ============================================ */
  function loadToday() {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    selectedDate = `${yyyy}-${mm}-${dd}`;
  }


  function loadMore() {
    pageNum++;
    loadMyReservations(pageNum, false);
  }
</script>

<?php include_once('../tail.php'); ?>