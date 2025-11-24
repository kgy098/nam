<?php
include_once('./_common.php');

$mode = $_GET['mode'] ?? 'student'; // student / teacher

if ($mode === 'teacher') {
    $sub_menu = "010200";     // 교사 메뉴권한
    $g5['title'] = "교사관리";
    $regPage = "./teacher_reg.php";
    $join_label = "입사일";
} else {
    $sub_menu = "010100";     // 학생 메뉴권한
    $g5['title'] = "학생관리";
    $regPage = "./member_reg.php";
    $join_label = "입실일";
}

auth_check_menu($auth, $sub_menu, 'r');

include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<div class="local_ov01 local_ov">
  <span class="btn_ov01"><span class="ov_txt">총 회원수 </span><span class="ov_num" id="totalCount">0명</span></span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">

    <div class="sch_left">
      <? if ($mode=='student') { ?>
      <input type="date" name="start_date" id="start_date" class="frm_input" style="width:140px">
      ~
      <input type="date" name="end_date" id="end_date" class="frm_input" style="width:140px">
      <button type="button" id="btnDateReset" class="btn btn_brown">날짜초기화</button>
      <? } ?>

      <select name="field" id="field">
        <option value="mb_name">이름</option>
      </select>

      <input type="text" name="keyword" id="keyword" class="frm_input" placeholder="검색어 입력">

      <button type="button" class="btn_submit" id="btnSearch">검색</button>
    </div>

    <div class="sch_right">
      <? if ($mode=='student') { ?>
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
        <? if ($mode=='student') { ?><th>반</th><? } ?>
        <th>연락처</th>
        <th><?= $join_label ?></th>
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
let MODE = "<?= $mode ?>";

$(function() {

  listMember(1);

  $("#btnSearch").click(() => listMember(1));
  $("#keyword").keyup(e => { if (e.keyCode === 13) listMember(1); });

  $("#btnDateReset").click(() => {
    $("#start_date").val('');
    $("#end_date").val('');
    listMember(1);
  });

  $("#btnExcel").click(() => {
    let qs = $("#frmSearch").serialize();
    location.href = './member_excel.php?' + qs;
  });

  $("#btnAddMember").click(() => {
    location.href = (MODE === "teacher") ? "<?= $regPage ?>?mode=teacher" : "<?= $regPage ?>";
  });

});


/* ==========================================================
   회원 목록 조회
========================================================== */
function listMember(page = 1) {

  let params = {
    mode: MODE,
    page: page,
    field: $("#field").val(),
    keyword: $("#keyword").val(),
    start_date: $("#start_date").val(),
    end_date: $("#end_date").val()
  };

  // API 내부에서 type=MEMBER_LIST 자동 설정됨
  memberAPI.list(params).then(function(res) {
      memberListCallback(res);
  });
}


/* ==========================================================
   리스트 콜백
========================================================== */
function memberListCallback(res) {

  if (!res || res.result !== 'SUCCESS') {
    alert("목록 불러오기 실패");
    return;
  }

  const list  = res.data.list  || [];
  const total = res.data.total || 0;
  const tbody = $("#memberTable tbody");

  $("#totalCount").text(total + '명');
  tbody.empty();

  if (list.length === 0) {
    tbody.append('<tr><td colspan="8" class="empty_table">자료가 없습니다.</td></tr>');
    return;
  }

  list.forEach(row => {

    if (MODE === 'teacher') {

      tbody.append(`
        <tr class="item" data-id="${row.mb_id}">
          <td>${row.mb_name}</td>
          <td>${row.mb_hp || '-'}</td>
          <td>${row.join_date ? row.join_date.substring(0,10) : '-'}</td>
        </tr>
      `);

    } else {

      tbody.append(`
        <tr class="item" data-id="${row.mb_id}">
          <td>${row.mb_name}</td>
          <td>${row.class || '-'}</td>
          <td>${row.mb_hp || '-'}</td>
          <td>${row.mb_datetime ? row.mb_datetime.substring(0,10) : '-'}</td>
        </tr>
      `);
    }
  });

  setPagination(total, res.data.page);

  /* --------------------------------------------------
     🔥 리스트 행 클릭 → 수정 페이지 이동
  -------------------------------------------------- */
  $("#memberTable .item").off("click").on("click", function () {
      const mb_id = $(this).data("id");

      if (MODE === "teacher") {
        location.href = "./teacher_reg.php?w=u&mb_id=" + mb_id;
      } else {
        location.href = "./member_reg.php?w=u&mb_id=" + mb_id;
      }
  });
}


/* ==========================================================
   페이지네이션
========================================================== */
function setPagination(total, currentPage = 1) {
  const rows = 20;
  const totalPage = Math.ceil(total / rows);
  let html = "";

  for (let i = 1; i <= totalPage; i++) {
    html += `<a href="#" class="pg_page ${i==currentPage?'on':''}" data-page="${i}">${i}</a>`;
  }

  $("#pagination").html(html);

  $(".pg_page").click(function(e) {
    e.preventDefault();
    listMember($(this).data("page"));
  });
}
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>
