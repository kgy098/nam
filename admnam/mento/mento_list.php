<?php
include_once('./_common.php');

$sub_menu = "030400"; // 원하는 메뉴코드로 변경
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = "멘토상담 관리";
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<link rel="stylesheet" href="<?= G5_THEME_URL ?>/nam/css/common.css">
<script src="<?= G5_API_URL ?>/api_consult.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<style>
  .tbl_head01 td {
    cursor: pointer;
  }
</style>

<!-- =============================== -->
<!-- 상단 통계 -->
<!-- =============================== -->
<div class="local_ov01 local_ov">
  <span class="btn_ov01">
    <span class="ov_txt">총 상담 수</span>
    <span class="ov_num" id="totalCount">0건</span>
  </span>
</div>

<!-- =============================== -->
<!-- 검색 영역 -->
<!-- =============================== -->
<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">

      <!-- 날짜 -->
      <input type="date" id="date_from" class="frm_input" style="width:140px">
      ~
      <input type="date" id="date_to" class="frm_input" style="width:140px">
      <button type="button" id="btnDateReset" class="btn btn_brown">날짜초기화</button>

      <!-- 반 -->
      <select id="classFilter" class="frm_input">
        <option value="">전체반</option>
      </select>

      <!-- 검색어 -->
      <input type="text" id="keyword" class="frm_input" placeholder="학생명/교사명 검색">

      <button type="button" class="btn_submit" id="btnSearch">검색</button>
    </div>

  </form>
  <div style="clear: both;"></div>
</div>

<!-- =============================== -->
<!-- 테이블 -->
<!-- =============================== -->
<div class="tbl_head01 tbl_wrap">
  <table id="consultTable">
    <thead>
      <tr>
        <th style="width:70px">번호</th>
        <th style="width:120px">학생명</th>
        <th style="width:80px">반</th>
        <th style="width:120px">교사명</th>
        <th style="width:130px">요청일</th>
        <th style="width:130px">상담일정</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- =============================== -->
<!-- 페이지네이션 -->
<!-- =============================== -->
<div class="pg_wrap">
  <div id="pagination"></div>
</div>

<script>
  $(function () {
    loadClassList();   // 반 목록
    loadList(1);       // 초기 리스트

    // 검색 버튼
    $("#btnSearch").on("click", function () {
      loadList(1);
    });

    // 엔터 검색
    $("#keyword").on("keyup", function (e) {
      if (e.keyCode === 13) loadList(1);
    });

    // 날짜 초기화
    $("#btnDateReset").on("click", function () {
      $("#date_from").val('');
      $("#date_to").val('');
      loadList(1);
    });

    // 테이블 row 클릭 → 상세 페이지 이동
    // $(document).on("click", ".row-item", function () {
    //   var id = $(this).data("id");
    //   location.href = "./consult_reg.php?w=u&id=" + id;
    // });
  });

  /* -----------------------------------------------------------
   * 반 목록 로드
   * --------------------------------------------------------- */
  function loadClassList() {
    apiClass.list(1, 200).then(function (res) {
      var list = res.data || [];
      var $sel = $("#classFilter");

      $sel.empty();
      $sel.append('<option value="">전체반</option>');

      list.forEach(function (row) {
        $sel.append(`<option value="${row.id}">${row.name}</option>`);
      });
    }).fail(function () {
      console.warn("반 로딩 실패");
    });
  }

  /* -----------------------------------------------------------
   * 리스트 로딩
   * --------------------------------------------------------- */
  function loadList(page) {

    var filters = {
      date_from: $("#date_from").val(),
      date_to: $("#date_to").val(),
      class: $("#classFilter").val(),
      keyword: $("#keyword").val(),
      type: '멘토상담'
    };

    ConsultAPI.list(page, 20, filters)
      .then(function (res) {
        var list = res.data.list || [];
        var total = res.data.total || 0;
        var currPage = res.data.page || 1;

        $("#totalCount").text(total + "건");

        var tbody = $("#consultTable tbody");
        tbody.empty();

        if (list.length === 0) {
          tbody.append('<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>');
          return;
        }

        list.forEach(function (row, idx) {
          var rowNum = total - ((currPage - 1) * 20 + idx);

          tbody.append(`
            <tr class="row-item" data-id="${row.id}">
              <td>${rowNum}</td>
              <td>${row.student_name || '-'}</td>
              <td>${row.class_name || '-'}</td>
              <td>${row.teacher_name || '-'}</td>
              <td>${row.requested_dt ? row.requested_dt.substring(0, 16) : '-'}</td>
              <td>${row.scheduled_dt ? row.scheduled_dt.substring(0, 16) : '-'}</td>
            </tr>
          `);
        });

        setPagination(total, currPage);

      })
      .fail(function (err) {
        alert("데이터 로드 실패");
        console.error(err);
      });
  }

  /* -----------------------------------------------------------
   * 페이지네이션
   * --------------------------------------------------------- */
  function setPagination(total, page) {
    var rows = 20;
    var totalPage = Math.ceil(total / rows);
    var block = 10;
    var currBlock = Math.ceil(page / block);
    var startPage = (currBlock - 1) * block + 1;
    var endPage = Math.min(startPage + block - 1, totalPage);

    var html = "";

    if (startPage > 1) {
      html += `<a href="#" class="pg_page" data-page="${startPage - 1}">◀</a>`;
    }

    for (var i = startPage; i <= endPage; i++) {
      html += `<a href="#" class="pg_page ${i === page ? 'on' : ''}" data-page="${i}">${i}</a>`;
    }

    if (endPage < totalPage) {
      html += `<a href="#" class="pg_page" data-page="${endPage + 1}">▶</a>`;
    }

    $("#pagination").html(html);

    $(".pg_page").on("click", function (e) {
      e.preventDefault();
      var p = $(this).data("page");
      loadList(p);
    });
  }
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>
