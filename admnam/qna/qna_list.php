<?php
include_once('./_common.php');

$sub_menu = "030500";
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '비대면 질의응답 관리';
$regPage = './qna_reg.php';

include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<style>
/* 추가 CSS 필요 없음 — 기존 관리자 CSS(tbl_head01 등) 그대로 사용 */
</style>

<script src="<?= G5_API_URL ?>/api_qna.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<!-- ================================ -->
<!-- 상단 정보 요약 -->
<!-- ================================ -->
<div class="local_ov01 local_ov">
  <span class="btn_ov01">
    <span class="ov_txt">총 질의 수</span>
    <span class="ov_num" id="totalCount">0개</span>
  </span>
</div>

<!-- ================================ -->
<!-- 검색 폼 -->
<!-- ================================ -->
<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">

      <!-- 날짜 -->
      <!-- <input type="date" name="date_from" id="date_from" class="frm_input" style="width:140px">
      ~
      <input type="date" name="date_to" id="date_to" class="frm_input" style="width:140px">
      <button type="button" id="btnDateReset" class="btn btn_brown">날짜초기화</button> -->

      <!-- 반 선택 -->
      <!-- <select name="class" id="class" class="frm_input">
        <option value="">전체반</option>
      </select> -->

      <!-- 키워드 검색 -->
      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="제목/내용/학생명 검색">

      <button type="button" class="btn_submit" id="btnSearch">검색</button>
    </div>

    <div class="sch_right">
      <!-- <button type="button" class="btn_03" id="btnAddQna">질문 등록</button> -->
    </div>

    <div style="clear:both;"></div>
  </form>
</div>

<!-- ================================ -->
<!-- 리스트 테이블 -->
<!-- ================================ -->
<div class="tbl_head01 tbl_wrap">
  <table id="qnaTable">
    <thead>
      <tr>
        <th style="width:80px">번호</th>
        <th style="width:100px">학생명</th>
        <th style="width:60px">반</th>
        <th>제목</th>
        <th style="width:100px">선생님</th>
        <th style="width:100px">답변여부</th>
        <th style="width:120px">등록일</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<!-- ================================ -->
<!-- 페이지네이션 -->
<!-- ================================ -->
<div class="pg_wrap">
  <div id="pagination"></div>
</div>

<script>
$(function() {

  // 반 목록 불러오기
  loadClassList();

  // 초기 로딩
  loadQnaList(1);

  $("#btnSearch").on("click", function() {
    loadQnaList(1);
  });

  $('#keyword').on('keyup', function(e) {
    if (e.keyCode === 13) {
      loadQnaList(1);
    }
  });

  $('#btnDateReset').on('click', function() {
    $('#date_from').val('');
    $('#date_to').val('');
    loadQnaList(1);
  });

  $("#btnAddQna").on("click", function() {
    location.href = "<?= $regPage ?>";
  });

  // 테이블 클릭 → 상세 수정 페이지
  $(document).on("click", ".item", function() {
    const id = $(this).data("id");
    location.href = "<?= $regPage ?>?w=u&id=" + id;
  });

});


// --------------------------------------------
// 반 목록
// --------------------------------------------
function loadClassList() {
  apiClass.list(1, 200)
    .then(function(res) {
      const list = res.data || [];
      const $sel = $("#class");
      $sel.empty();
      $sel.append('<option value="">전체반</option>');

      list.forEach(function(row) {
        $sel.append(`<option value="${row.id}">${row.name}</option>`);
      });
    })
    .fail(function(err) {
      console.warn("반 목록 로딩 실패", err);
    });
}


// --------------------------------------------
// Q&A 리스트
// --------------------------------------------
function loadQnaList(page = 1) {

  const filters = {
    type: 'QNA_LIST',
    page: page,
    rows: 20,
    keyword: $("#keyword").val(),
    class: $("#class").val(),
    date_from: $("#date_from").val(),
    date_to: $("#date_to").val()
  };

  QnaAPI.list(filters)
    .then(function(res) {

      const list = res.data.list || [];
      const total = res.data.total || 0;
      const currentPage = res.data.page || 1;
      const rows = res.data.rows || 20;

      $("#totalCount").text(total + "개");

      const tbody = $("#qnaTable tbody");
      tbody.empty();

      if (!list.length) {
        tbody.append('<tr><td colspan="7" class="empty_table">자료가 없습니다.</td></tr>');
        return;
      }

      list.forEach(function(row, idx) {
        const rowNum = total - ((currentPage - 1) * rows + idx);

        console.log(rowNum);
        tbody.append(`
          <tr class="item" data-id="${row.id}" style="cursor:pointer;">
            <td>${rowNum}</td>
            <td>${row.student_name || '-'}</td>
            <td>${row.class_name || '-'}</td>
            <td style="text-align:left; padding-left:10px;">${row.title || '-'}</td>
            <td>${row.teacher_name || '-'}</td>
            <td>${row.status || '-'}</td>
            <td>${(row.reg_dt || '').substring(0,10)}</td>
          </tr>
        `);
      });

      setPagination(total, currentPage);

    })
    .fail(function(err) {
      console.error(err);
      alert("데이터 로드 실패");
    });
}


// --------------------------------------------
// 페이지네이션
// --------------------------------------------
function setPagination(total, currentPage = 1) {

  const rows = 20;
  const totalPage = Math.ceil(total / rows);
  const pageBlock = 10;
  const currentBlock = Math.ceil(currentPage / pageBlock);

  const startPage = (currentBlock - 1) * pageBlock + 1;
  const endPage = Math.min(startPage + pageBlock - 1, totalPage);

  let html = '';

  if (currentBlock > 1) {
    html += `<a href="#" class="pg_page" data-page="${startPage - 1}">◀</a>`;
  }

  for (let i = startPage; i <= endPage; i++) {
    html += `<a href="#" class="pg_page ${i == currentPage ? 'on' : ''}" data-page="${i}">${i}</a>`;
  }

  if (endPage < totalPage) {
    html += `<a href="#" class="pg_page" data-page="${endPage + 1}">▶</a>`;
  }

  $("#pagination").html(html);

  $(".pg_page").on("click", function(e) {
    e.preventDefault();
    loadQnaList($(this).data("page"));
  });
}

</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>
