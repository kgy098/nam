<?php
include_once('./_common.php');
$sub_menu = "020100"; // 공지사항 메뉴코드(원하면 변경)
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '공지사항';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_notice.js"></script>

<div class="local_ov01 local_ov">
  <span class="btn_ov01"><span class="ov_txt">총 공지사항 </span><span class="ov_num" id="totalCount">0건</span></span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">
      <select name="field" id="field">
        <option value="title">제목</option>
      </select>

      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="검색어 입력">
      <button type="button" class="btn_submit" id="btnSearch">검색</button>

    </div>

    <div class="sch_right">
      <button type="button" class="btn_03" id="btnAddNotice">공지등록</button>
    </div>

  </form>
  <div style="clear: both;"></div>
</div>

<div class="tbl_head01 tbl_wrap">
  <table id="noticeTable">
    <thead>
      <tr>
        <th>번호</th>
        <th>제목</th>
        <th>작성자</th>
        <th>등록일</th>
      </tr>
    </thead>
    <tbody></tbody>
  </table>
</div>

<div class="pg_wrap">
  <div id="pagination"></div>
</div>

<script>
  $(function () {

    loadNoticeList();

    $("#btnSearch").on("click", function () {
      loadNoticeList();
    });

    $("#keyword").on("keyup", function (e) {
      if (e.keyCode === 13) loadNoticeList(1);
    });

    $('#btnDateReset').on('click', function(){
      $("#start_date").val('');
      $("#end_date").val('');
      loadNoticeList(1);
    });

    $('#btnExcel').on('click', function(){
      let qs = $('#frmSearch').serialize();
      location.href = './notice_excel.php?' + qs;
    });

    $('#btnAddNotice').on('click', function(){
      location.href = './notice_reg.php';
    });

  });

  function loadNoticeList(page = 1) {

    const params = {
      type: 'NOTICE_LIST',
      page: page,
      field: $("#field").val(),
      keyword: $("#keyword").val(),
      start_date: $("#start_date").val(),
      end_date: $("#end_date").val()
    };

    NoticeAPI.list(page, 20).then(function(res){

      const tbody = $("#noticeTable tbody");
      tbody.empty();

      if (!res.data || !res.data.list.length) {
        $("#totalCount").text('0건');
        tbody.append('<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>');
        return;
      }

      const list = res.data.list;
      const total = res.data.total;

      $("#totalCount").text(total + "건");

      list.forEach(row => {
        tbody.append(`
          <tr class="item" data-id="${row.id}">
            <td>${row.id}</td>
            <td class="td_left">${row.title}</td>
            <td>${row.writer_name || '-'}</td>
            <td>${row.reg_dt?.substring(0,10) || '-'}</td>
          </tr>
        `);
      });

      $("#noticeTable tbody tr").on("click", function () {
        const id = $(this).data("id");
        location.href = "./notice_reg.php?w=u&id=" + id;
      });

      setPagination(total, page);
    });
  }

  function setPagination(total, currentPage = 1) {
    const rows = 20;
    const totalPage = Math.ceil(total / rows);
    let html = '';

    for (let i = 1; i <= totalPage; i++) {
      html += `<a href="#" class="pg_page ${i == currentPage ? 'on' : ''}" data-page="${i}">${i}</a>`;
    }

    $("#pagination").html(html);

    $(".pg_page").on("click", function (e) {
      e.preventDefault();
      loadNoticeList($(this).data("page"));
    });
  }

</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>
