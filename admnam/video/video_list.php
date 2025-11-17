<?php
$sub_menu = '040100'; // 필요시 변경
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '수업영상';
include_once(G5_NAM_ADM_APTH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_video.js"></script>

<div class="local_ov01 local_ov">
  <span class="btn_ov01"><span class="ov_txt">총 수업영상 </span><span class="ov_num" id="totalCount">0건</span></span>
</div>

<div class="btn_add01 btn_add">
  <a href="./video_reg.php" class="btn btn_02">영상등록</a>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th scope="col" width="60">No</th>
        <th scope="col">제목</th>
        <th scope="col" width="120">등록자 이름</th>
        <th scope="col" width="150">등록일시</th>
      </tr>
    </thead>
    <tbody id="videoList"></tbody>
  </table>
</div>

<div id="pagination" class="pg_wrap"></div>

<script>
let currentPage = 1;
let rows = 10; // 화면설계서에서는 1페이지에 5줄이었지만 G5 기본은 10개 기준으로 둠

/* 목록 호출 */
function loadList(page) {
  currentPage = page;

  VideoAPI.list(page, rows, { }).then(function(res){

    let list = res.data.list;
    let total = res.data.total;
    let html = '';

    if (!list || list.length === 0) {
      html = '<tr><td colspan="4" class="empty_table">자료가 없습니다.</td></tr>';
      $("#videoList").html(html);
      $("#pagination").html('');
      return;
    }

    for (let i=0; i<list.length; i++) {
      let v = list[i];
      html += `
        <tr>
          <td>${v.id}</td>
          <td><a href="./video_reg.php?w=u&id=${v.id}">${v.title}</a></td>
          <td>${v.mb_id ?? '관리자'}</td>
          <td>${v.reg_dt}</td>
        </tr>
      `;
    }

    $("#videoList").html(html);
    drawPagination(total, page, rows);
    $("#totalCount").text(total);

  }).catch(function(){
    alert("목록을 불러오지 못했습니다.");
  });
}

/* 페이징 */
function drawPagination(total, page, rows) {
  let totalPage = Math.ceil(total / rows);
  if (totalPage <= 1) {
    $("#pagination").html('');
    return;
  }

  let html = '<div class="pg">';

  for (let i = 1; i <= totalPage; i++) {
    if (i === page) html += `<strong>${i}</strong>`;
    else html += `<a href="javascript:loadList(${i});">${i}</a>`;
  }

  html += '</div>';
  $("#pagination").html(html);
}

$(function(){
  loadList(1);
});
</script>

<?php
include_once(G5_NAM_ADM_APTH . '/admin.tail.php');
?>
