<?php
include_once('./_common.php');
$sub_menu = "020100";
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '학사일정';
include_once(G5_ADMIN_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_member.js"></script>

<div class="local_ov01 local_ov">
  <span class="btn_ov01"><span class="ov_txt">총 회원수 </span><span class="ov_num" id="totalCount">0명</span></span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">
      <input type="date" name="start_date" id="start_date" class="frm_input" style="width:140px">
      ~
      <input type="date" name="end_date" id="end_date" class="frm_input" style="width:140px">
      <button type="button" id="btnDateReset" class="btn btn_brown">날짜초기화</button>
      <select name="field" id="field">
        <option value="mb_name">이름</option>
      </select>
      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="검색어 입력">
      <button type="button" class="btn_submit" id="btnSearch">검색</button>
    </div>

    <div class="sch_right">
      <button type="button" class="btn_02" id="btnExcel">엑셀 다운로드</button>
      <button type="button" class="btn_03" id="btnAddMember">회원등록</button>
    </div>

  </form>
  <div style="clear: both;"></div>
</div>


<div class="tbl_head01 tbl_wrap">
  <table id="memberTable">
    <thead>
      <tr>
        <th>이름</th>
        <th>반</th>
        <th>연락처</th>
        <th>가입일</th>
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
    loadMemberList();

    $("#btnSearch").on("click", function() {
      loadMemberList();
    });

    $('#keyword').on('keyup', function(e){
        if(e.keyCode === 13){
            loadMemberList(1);  // 엔터로 검색
        }
    });

    $('#btnDateReset').on('click', function(){
        // 날짜만 초기화
        $('input[name=start_date]').val('');
        $('input[name=end_date]').val('');

        loadMemberList(1); // 1페이지부터 다시 조회
    });
  });

  function loadMemberList(page = 1) {
    // const params = {
    //   type: 'MEMBER_LIST',
    //   page: page,
    //   field: $("#field").val(),
    //   keyword: $("#keyword").val()
    // };
    const params = {
      type: 'MEMBER_LIST',
      page: page,
      field: $("#field").val(),
      keyword: $("#keyword").val(),
      start_date: $("#start_date").val(),
      end_date: $("#end_date").val()
    };

    apiMemberList(params).done(function(res) {
      if (res.result === 'SUCCESS') {
        const list = res.data.list || [];
        const total = res.data.total || 0;
        $("#totalCount").text(total + '명');

        const tbody = $("#memberTable tbody");
        tbody.empty();

        if (!list.length) {
          tbody.append('<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>');
          return;
        }

        list.forEach(row => {
          tbody.append(`
          <tr class="item" data-id="${row.mb_id}">
            <td>${row.mb_name}</td>
            <td>${row.class}</td>
            <td>${row.mb_hp || '-'}</td>
            <td>${row.mb_datetime?.substring(0, 10) || '-'}</td>
          </tr>
        `);
        });

        $(".btnView").on("click", function() {
          const id = $(this).data("id");
          location.href = "./member_form.php?mb_id=" + id;
        });

        $(".btnConfirm").on("click", function() {
          const id = $(this).data("id");
          const current = $(this).data("status");
          const newStatus = current == 1 ? 0 : 1;
          apiMemberUpdate(id, {
            mb_confirm: newStatus
          }).done(function(r) {
            if (r.result === 'SUCCESS') loadMemberList(page);
          });
        });

        setPagination(total, page);
      }
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
    $(".pg_page").on("click", function(e) {
      e.preventDefault();
      loadMemberList($(this).data("page"));
    });
  }
</script>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
?>