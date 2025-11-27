<?php
include_once('./_common.php');
$sub_menu = "040200";
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '학습보고서';
$regPage = './study_report_reg.php';

include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<style>
</style>

<script src="<?= G5_API_URL ?>/api_study_report.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<div class="local_ov01 local_ov">
  <span class="btn_ov01">
    <span class="ov_txt">총 보고서 수</span>
    <span class="ov_num" id="totalCount">0개</span>
  </span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">
      <input type="date" name="date_from" id="date_from" class="frm_input" style="width:140px">
      ~
      <input type="date" name="date_to" id="date_to" class="frm_input" style="width:140px">
      <button type="button" id="btnDateReset" class="btn btn_brown">날짜초기화</button>

      <select name="class" id="class" class="frm_input">
        <option value="">전체반</option>
      </select>

      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="제목/내용/학생명 검색">
      <button type="button" class="btn_submit" id="btnSearch">검색</button>
    </div>

    <div class="sch_right">
      <button type="button" class="btn_03" id="btnAddReport">보고서 등록</button>
    </div>

  </form>
  <div style="clear: both;"></div>
</div>

<div class="tbl_head01 tbl_wrap">
  <table id="reportTable">
    <thead>
      <tr>
        <th style="width:80px">번호</th>
        <th style="width:100px">학생명</th>
        <th style="width:60px">반</th>
        <th style="width:150px">과목</th>
        <th>제목</th>
        <th style="width:100px">시험일</th>
        <th style="width:100px">등록일</th>
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
    // 반 목록 로드
    loadClassList();

    // 초기 리스트 로드
    loadReportList(1);

    $("#btnSearch").on("click", function() {
      loadReportList(1);
    });

    $('#keyword').on('keyup', function(e) {
      if (e.keyCode === 13) {
        loadReportList(1);
      }
    });

    $('#btnDateReset').on('click', function() {
      $('#date_from').val('');
      $('#date_to').val('');
      loadReportList(1);
    });

    $('#btnAddReport').on('click', function() {
      location.href = "<?= $regPage ?>";
    });

    // 테이블 행 클릭 시 상세 페이지로 이동
    $(document).on('click', '.item', function() {
      const id = $(this).data('id');
      location.href = "<?= $regPage ?>?w=u&id=" + id;
    });
  });

  // 반 목록 불러오기
  function loadClassList() {
    apiClass.list(1, 100)
      .then(function(res) {
        const list = res.data || [];
        const $sel = $('#class');

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

  function loadReportList(page = 1) {
    const filters = {
      class: $("#class").val(),
      date_from: $("#date_from").val(),
      date_to: $("#date_to").val(),
      keyword: $("#keyword").val()
    };

    StudyReportAPI.list(page, 20, filters).done(function(res) {
      if (res.result === 'SUCCESS') {
        const list = res.data.list || [];
        const total = res.data.total || 0;
        const currentPage = res.data.page || 1;

        $("#totalCount").text(total + '개');

        const tbody = $("#reportTable tbody");
        tbody.empty();

        if (!list.length) {
          tbody.append('<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>');
          return;
        }

        list.forEach((row, index) => {
          const rowNum = total - ((currentPage - 1) * 20 + index);
          const fileIcon = row.file_count > 0 ? `📎 ${row.file_count}` : '-';
          const subjectText =
            (row.subject_type ? row.subject_type + ' - ' : '') +
            (row.subject_name || '-');

          tbody.append(`
          <tr class="item" data-id="${row.id}" style="cursor:pointer;">
            <td>${rowNum}</td>
            <td>${row.mb_name || '-'}</td>
            <td>${row.class || '-'}</td>
            <td>${subjectText}</td>
            <td style="text-align:left; padding-left:10px;">${row.title || '-'}</td>
            <td>${row.report_date || '-'}</td>
            <td>${row.reg_dt?.substring(0,10) || '-'}</td>
          </tr>
        `);
        });

        setPagination(total, currentPage);
      }
    }).fail(function(err) {
      alert('데이터 로드 실패');
      console.error(err);
    });
  }

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
      loadReportList($(this).data("page"));
    });
  }
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>