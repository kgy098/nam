<?php
include_once('./_common.php');
$sub_menu = "010100";
auth_check_menu($auth, $sub_menu, 'r');

$mode = $_GET['mode'] ?? 'student'; // 기본은 학생
if ($mode === 'teacher') {
  $g5['title'] = '교사관리';
  $regPage = './teacher_reg.php';
  $listType = 'TEACHER_LIST';
  $sub_menu = "010200";
} else {
  $g5['title'] = '학생관리';
  $regPage = './member_reg.php';
  $listType = 'MEMBER_LIST';
}

include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<div class="local_ov01 local_ov">
  <span class="btn_ov01"><span class="ov_txt">총 회원수 </span><span class="ov_num" id="totalCount">0명</span></span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">
      <? if ( $mode=='student' ) { ?>
      <input type="date" name="start_date" id="start_date" class="frm_input" style="width:140px">
      ~
      <input type="date" name="end_date" id="end_date" class="frm_input" style="width:140px">
      <button type="button" id="btnDateReset" class="btn btn_brown">날짜초기화</button>
      <? } ?>
      <select name="field" id="field">
        <option value="mb_name">이름</option>
      </select>
      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="검색어 입력">
      <button type="button" class="btn_submit " value="검색" id="btnSearch">검색</button>
    </div>

    <div class="sch_right">
      <? if ( $mode=='student' ) { ?>
      <button type="button" class="btn_02" id="btnExcel">엑셀 다운로드</button>
      <? } ?>
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
        <? if ( $mode=='student' ) { ?>
        <th>반</th>
        <? } ?>
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


<script src="<?= G5_API_URL ?>/api_member.js"></script>

<script>
  var MODE = "<?= $mode ?>";

  $(function() {

    // 최초 로딩
    listMember(1);

    $("#btnSearch").on("click", function() {
      listMember(1);
    });

    $('#keyword').on('keyup', function(e) {
      if (e.keyCode === 13) listMember(1);
    });

    $('#btnDateReset').on('click', function() {
      $('#start_date').val('');
      $('#end_date').val('');
      listMember(1);
    });

    $('#btnExcel').on('click', function() {
      let qs = $('#frmSearch').serialize();
      location.href = './member_excel.php?' + qs;
    });

    $('#btnAddMember').on('click', function() {
      if (MODE === "teacher") {
        location.href = "<?= $regPage ?>?mode=teacher";
      } else {
        location.href = "<?= $regPage ?>";
      }
    });

  });

  /* ==========================================================
     회원 목록 조회 (loadMemberList → listMember)
  ========================================================== */
  function listMember(page = 1) {

    const listType = (MODE === 'student') ? 'STUDENT_LIST' : 'TEACHER_LIST';

    const params = {
      type: listType,
      mode: MODE,
      page: page,
      field: $("#field").val(),
      keyword: $("#keyword").val(),
      start_date: $("#start_date").val(),
      end_date: $("#end_date").val()
    };

    memberAPI.list(params);  // ⭐ 핵심 변경: apiMemberList 제거
  }


  /* ==========================================================
     리스트 콜백
     (api_member.js → memberListCallback(res))
  ========================================================== */
  function memberListCallback(res) {

    if (!res || res.result !== 'SUCCESS') {
      alert("목록 불러오기 실패");
      return;
    }

    const list  = res.data.list  || [];
    const total = res.data.total || 0;

    $("#totalCount").text(total + '명');

    const tbody = $("#memberTable tbody");
    tbody.empty();

    if (!list.length) {
      tbody.append('<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>');
      return;
    }

    list.forEach(row => {

      if (MODE === 'teacher') {
        tbody.append(`
          <tr class="item" data-id="${row.mb_id}">
            <td>${row.mb_name}</td>
            <td>${row.mb_hp || '-'}</td>
            <td>${row.join_date?.substring(0,10) || '-'}</td>
          </tr>
        `);
      } else {
        tbody.append(`
          <tr class="item" data-id="${row.mb_id}">
            <td>${row.mb_name}</td>
            <td>${row.class || '-'}</td>
            <td>${row.mb_hp || '-'}</td>
            <td>${row.mb_datetime?.substring(0,10) || '-'}</td>
          </tr>
        `);
      }
    });

    setPagination(total, res.data.page || 1);
  }


  /* ==========================================================
     페이지네이션
  ========================================================== */
  function setPagination(total, currentPage = 1) {
    const rows = 20;
    const totalPage = Math.ceil(total / rows);
    let html = '';

    for (let i = 1; i <= totalPage; i++) {
      html += `<a href="#" class="pg_page ${i==currentPage?'on':''}" data-page="${i}">${i}</a>`;
    }

    $("#pagination").html(html);

    $(".pg_page").on("click", function(e) {
      e.preventDefault();
      listMember($(this).data("page"));
    });
  }

</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>