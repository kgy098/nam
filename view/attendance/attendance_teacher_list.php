<?php
include_once('./_common.php');

$menu_group = 'att';
$g5['title'] = "출결현황";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");
?>

<script src="<?= G5_API_URL ?>/api_attendance.js"></script>
<script src="<?= G5_API_URL ?>/api_attendance_type.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<style>
  .missing {
    color: #E0558E;
    font-weight: 600;
  }
</style>

<!-- ============================
     상단 검색 영역
============================ -->
<div class="common-section">

  <div class="common-form-row first-row">

    <!-- 출결구분 -->
    <div class="common-select-box">
      <select id="attendType" class="common-select">
        <option value="">출결구분선택</option>
      </select>
    </div>

    <!-- 날짜 -->
    <input type="date" id="start_date" class="common-input-date">
    <input type="date" id="end_date" class="common-input-date">

  </div>

  <div class="common-form-row second-row">

    <!-- 반 -->
    <div class="common-select-box" style="flex:1;">
      <select id="class_id" class="common-select">
        <option value="">반선택</option>
      </select>
    </div>

    <!-- 검색 버튼 -->
    <button class="common-form-btn" onclick="search()">검색</button>
  </div>

</div>

<!-- ============================
     리스트 영역
============================ -->
<div class="common-list-container" id="attList"></div>

<!-- 더보기 -->
<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>

<script>
  /*************************************************
   * 페이징 변수
   *************************************************/
  let page = 1;
  const num = 20;
  let loading = false;

  /*************************************************
   * 오늘 날짜
   *************************************************/
  function todayYmd() {
    let d = new Date();
    let m = ('0' + (d.getMonth() + 1)).slice(-2);
    let dd = ('0' + d.getDate()).slice(-2);
    return d.getFullYear() + '-' + m + '-' + dd;
  }

  /*************************************************
   * 초기값 설정
   *************************************************/
  $('#start_date').val(todayYmd());
  $('#end_date').val(todayYmd());

  /*************************************************
   * 검색
   *************************************************/
  function search() {
    page = 1;
    $('#attList').empty();
    loadList();
  }

  /*************************************************
   * 더보기
   *************************************************/
  function loadMore() {
    if (loading) return;
    page++;
    loadList();
  }

  /*************************************************
   * 리스트 로딩
   *************************************************/
  function loadList() {

    if (loading) return;
    loading = true;

    const start_date = $('#start_date').val();
    const end_date = $('#end_date').val();

    if (!start_date || !end_date) {
      alert("기간을 선택하세요.");
      loading = false;
      return;
    }

    AttendanceAPI.adminList({
        start_date,
        end_date,
        class_id: $('#class_id').val(),
        attend_type_id: $('#attendType').val(),
        page,
        num
      })
      .then(res => {

        const list = res.data.list || [];
        const total = res.data.count || 0;

        // 더보기 버튼 처리
        if (page * num < total) {
          $('#moreWrap').show();
        } else {
          $('#moreWrap').hide();
        }

        // 데이터 없을 때
        if (page === 1 && list.length === 0) {
          $('#attList').html(`
          <div class="common-empty">검색 결과가 없습니다.</div>
        `);
          loading = false;
          return;
        }

        list.forEach(row => {
          $('#attList').append(renderAttendItem(row));
        });



        loading = false;

      })
      .fail(() => {
        if (page === 1) {
          $('#attList').html(`<div class="common-empty">조회 실패</div>`);
        }
        loading = false;
      });
  }

  /* ==========================
   출결 리스트 한 줄 템플릿
   (모의고사 응시 UI 스타일 적용)
  ========================== */
  function renderAttendItem(row) {

    const isAttend = row.status === '출석완료';

    const statusLabel = isAttend ? '출석완료' : '미출석';
    const statusClass = isAttend ? 'gold' : 'gray';

    const className = row.class_name || '-';
    const studentName = row.mb_name || '';
    const attendType = row.attend_type_name || '-';

    // 표시 날짜
    const dateText = isAttend ?
      (row.attend_dt?.substring(0, 16) || row.date) :
      row.date;

    // 출석하기 버튼 (미출석일 때만)
    let actionBtn = '';
    if (!isAttend) {
      actionBtn = `
      <button class="common-form-btn btn_attend"
              data-mb="${row.mb_id}"
              data-type="${row.attend_type_id}"
              data-date="${row.date}" style="height:auto; padding:4px 10px; border-radius:5px; font-size:12px;">
        출석하기
      </button>
    `;
    }

    return `
    <div class="mock-item-box">

      <!-- 날짜 + 출결구분 -->
      <div style="font-size:13px;color:#D9D8D5E0;margin-bottom:4px; display:flex;justify-content:space-between;align-items:center;">
        <div>
          ${dateText}
          <span style="font-size:12px;color:#ADABA6E0;">(${attendType}) ${className} ${studentName}</span>
        </div>
        <div class="mock-status-badge ${statusClass}" style="position:static;">
          ${statusLabel}
        </div>
      </div>

      <!-- 출석하기 버튼 -->
      ${actionBtn ? `
        <div style="margin-top:8px;display:flex;justify-content:flex-end;">
          ${actionBtn}
        </div>` : ''}

    </div>
  `;
  }


  /*************************************************
   * 출석하기
   *************************************************/
  $(document).on('click', '.btn_attend', function() {

    const mb_id = $(this).data('mb');
    const type_id = $(this).data('type');
    const date = $(this).data('date');

    if (!confirm("출석 처리하시겠습니까?")) return;

    let now = new Date();
    let hh = ('0' + now.getHours()).slice(-2);
    let mm = ('0' + now.getMinutes()).slice(-2);
    let ss = ('0' + now.getSeconds()).slice(-2);

    const attend_dt = `${date} ${hh}:${mm}:${ss}`;

    AttendanceAPI.add(mb_id, {
        attend_type_id: type_id,
        date: attend_dt,
        status: '출석완료'
      })
      .then(() => {
        alert("출석 처리되었습니다.");
        search();
      })
      .fail(() => {
        alert("출석 처리 실패");
      });

  });

  /*************************************************
   * 출결구분 + 반 목록 로딩
   *************************************************/
  function loadAttendTypeList() {
    AttendanceTypeAPI.list(1, 100)
      .then(res => {
        const list = res.data.list || [];
        const $sel = $('#attendType');

        $sel.empty().append('<option value="">출결구분선택</option>');

        list.forEach(row => {
          $sel.append(`<option value="${row.id}">${row.name}</option>`);
        });
      });
  }

  function loadClassList() {
    apiClass.list(1, 100)
      .then(res => {
        const list = res.data || [];
        const $sel = $('#class_id');

        $sel.empty().append('<option value="">반선택</option>');

        list.forEach(row => {
          $sel.append(`<option value="${row.id}">${row.name}</option>`);
        });
      });
  }

  // 초기 로딩
  loadAttendTypeList();
  loadClassList();

  // 최초 검색 자동 실행
  search();
</script>

<?php include_once('../tail.php'); ?>