<?php
include_once('./_common.php');
$sub_menu = "030200"; // 라운지 예약현황 메뉴코드 (원하면 변경)
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '라운지 예약현황';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_lounge.js"></script>
<script src="<?= G5_API_URL ?>/api_lounge_seat.js"></script>
<script src="<?= G5_API_URL ?>/api_lounge_reservation.js"></script>

<div class="local_ov01 local_ov">
  <span class="btn_ov01"><span class="ov_txt">총 예약건수 </span><span class="ov_num" id="totalCount">0건</span></span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">

      <!-- 라운지 선택 -->
      <select id="lounge_id" name="lounge_id">
        <option value="">전체 라운지</option>
      </select>

      <!-- 날짜 선택 -->
      <input type="date" id="target_date" name="target_date" class="frm_input">
      <button type="button" class="btn_frmline" id="btnDateReset">초기화</button>

      <!-- 검색어 (학생명, 좌석번호) -->
      <select name="field" id="field">
        <option value="student_name">학생명</option>
        <option value="seat_no">좌석번호</option>
      </select>

      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="검색어 입력">

      <button type="button" class="btn_submit" id="btnSearch">검색</button>
    </div>

  </form>

  <div style="clear: both;"></div>
</div>

<div class="tbl_head01 tbl_wrap">
  <table id="rsvTable">
    <thead>
      <tr>
        <th>번호</th>
        <th>라운지</th>
        <th>좌석번호</th>
        <th>학생명</th>
        <th>예약일</th>
        <th>시작</th>
        <th>종료</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<div class="pg_wrap">
  <div id="pagination"></div>
</div>


<script>
  $(function() {
    $("#btnDateReset").on("click", function() {
      $("#target_date").val('');
      loadList(1);
    });

    loadLoungeList();
    loadList();

    $("#btnSearch").on("click", function() {
      loadList();
    });

    $("#keyword").on("keyup", function(e) {
      if (e.keyCode === 13) loadList(1);
    });

  });


  // ---------------------------------------------
  // 라운지 목록 로드
  // ---------------------------------------------
  function loadLoungeList() {
    loungeAPI.list(1, 100).then(function(res) {
      let html = '<option value="">전체 라운지</option>';
      res.data.forEach(v => {
        html += `<option value="${v.id}">${v.name}</option>`;
      });
      $("#lounge_id").html(html);
    });
  }


  // ---------------------------------------------
  // 예약현황 리스트 로드
  // ---------------------------------------------
  function loadList(page = 1) {

    const params = {
      page,
      lounge_id: $("#lounge_id").val(),
      target_date: $("#target_date").val(),
      field: $("#field").val(),
      keyword: $("#keyword").val()
    };

    // API 형식은 기존 ctrl_lounge_reservation.php 에서 LIST 타입 사용
    LoungeReservationAPI.list(page, 20, params).then(function(res) {

      const tbody = $("#rsvTable tbody");
      tbody.empty();

      console.log("total: " + res.total);
      if (!res.data || res.total===0) {
        $("#totalCount").text("0건");
        tbody.append('<tr><td colspan="7" class="empty_table">자료가 없습니다.</td></tr>');
        return;
      }

      const list = res.data;
      const total = res.total;

      $("#totalCount").text(total + "건");

      list.forEach(row => {
        tbody.append(`
        <tr>
          <td>${row.id}</td>
          <td>${row.lounge_name}</td>
          <td>${row.seat_no}</td>
          <td>${row.student_name}</td>
          <td>${row.reserved_date}</td>
          <td>${row.start_time}</td>
          <td>${row.end_time}</td>
        </tr>
      `);
      });

      setPagination(total, page);
    });
  }


  // ---------------------------------------------
  // 페이지네이션
  // ---------------------------------------------
  function setPagination(total, currentPage = 1) {
    const rows = 20;
    const totalPage = Math.ceil(total / rows);
    let html = "";

    for (let i = 1; i <= totalPage; i++) {
      html += `<a href="#" class="pg_page ${i == currentPage ? 'on' : ''}" data-page="${i}">${i}</a>`;
    }

    $("#pagination").html(html);

    $(".pg_page").on("click", function(e) {
      e.preventDefault();
      loadList($(this).data("page"));
    });
  }
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>