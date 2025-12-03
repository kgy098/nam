<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "비대면 질의응답";
include_once('../head.php');

$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");
?>

<link rel="stylesheet" href="<?= G5_THEME_URL ?>/nam/css/qna.css">

<!-- 리스트 -->
<div class="common-list-container" id="qnaList"></div>

<!-- 더보기 -->
<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>

<!-- 멘토 질의하기 -->
<div style="width:90%;max-width:420px;margin:30px auto 10px;font-size:17px;font-weight:600;color:#ffffffd0;">
  멘토 질의하기
</div>

<div class="common-section">

  <!-- 멘토 선택 -->
  <div class="common-form-row first-row">
    <div class="common-select-box">
      <select id="mentorSelect" class="common-select" style="width:80%;">
        <option value="">멘토 선택</option>
      </select>
    </div>
  </div>

  <!-- 질문 입력 -->
  <div class="common-form-row">
    <textarea id="qnaQuestion" class="common-input"
      style="height:120px;padding-top:12px;" placeholder="질의를 입력하세요."></textarea>
  </div>

  <!-- 등록 버튼 -->
  <div class="common-form-row">
    <button class="common-btn" onclick="registerQna()">질의 등록</button>
  </div>

</div>

<script src="<?= G5_API_URL ?>/api_qna.js"></script>
<script src="<?= G5_API_URL ?>/api_consult.js"></script>

<script>
var mb_id = "<?= $mb_id ?>";
var page = 1;
var loading = false;

/* ================================
      멘토 목록 로드
================================ */
$(document).ready(function() {
  loadMentorList();
  loadQnaList();
});

function loadMentorList() {
  ConsultAPI.teacherList().then(function(res) {

    var list = res.data || [];
    var sel = $("#mentorSelect");

    list.forEach(function(t) {
      sel.append(`<option value="${t.mb_id}">${t.mb_name}</option>`);
    });

  }).fail(function() {
    alert("멘토 목록을 불러오지 못했습니다.");
  });
}

/* ================================
      QNA 목록
================================ */
function loadQnaList() {
  if (loading) return;
  loading = true;

  QnaAPI.list({ page: page, rows: 10 }).then(function(res) {
    renderQnaList(res.data.list || []);

    if (res.data.total > page * 10) $("#moreWrap").show();
    else $("#moreWrap").hide();

    loading = false;
  }).fail(function() {
    alert("목록 조회 오류");
    loading = false;
  });
}

function loadMore() {
  page++;
  loadQnaList();
}

/* ================================
      리스트 렌더링
================================ */
function renderQnaList(list) {
  var wrap = $("#qnaList");

  list.forEach(function(row) {
    var id = row.id;
    var title = row.title ?? '';
    var status = row.status ?? '';
    var regdt = (row.reg_dt ?? '').substring(0, 10).replace(/-/g, '.');

    var badge = `
      <span class="mock-status-badge ${status === '답변완료' ? 'gold' : 'gray'}">
        ${status}
      </span>
    `;

    var html = `
      <div class="mock-item-box">

        <!-- 타이틀행 -->
        <div class="mock-title-row" onclick="toggleDetail(${id})">
          <div class="qna-title">${title}</div>
          ${badge}
        </div>

        <!-- 날짜 -->
        <div class="mock-meta">${regdt}</div>

        <!-- Arrow -->
        <div style="text-align:right;margin-top:6px;">
          <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
               class="common-arrow" id="arrow-${id}"
               onclick="toggleDetail(${id});event.stopPropagation();">
        </div>

        <!-- 답변영역 -->
        <div id="detail-${id}" class="mock-subject-list" style="display:none;"></div>
      </div>
    `;

    wrap.append(html);
  });
}

/* ================================
      상세 펼침
================================ */
function toggleDetail(id) {
  var box = $("#detail-" + id);
  var arrow = $("#arrow-" + id);

  if (box.is(":visible")) {
    box.slideUp(150);
    arrow.removeClass("open");
    return;
  }

  // Ajax 상세
  QnaAPI.get(id).then(function(res) {
    var row = res.data || {};
    var teacher = row.teacher_mb_id ?? '';
    var answeredDt = (row.answered_dt ?? '').substring(0, 10).replace(/-/g, '.');
    var answer = row.answer ?? '';

    var html = '';

    if (row.status === '답변완료') {
      html = `
        <div class="qna-answer-box">
          <div class="qna-answer-header">
            <span>↳ ${teacher} 선생님</span>
            <span>${answeredDt}</span>
          </div>
          <div>${answer}</div>
        </div>
      `;
    }

    box.html(html);
    box.slideDown(150);
    arrow.addClass('open');

  }).fail(function() {
    alert("상세 조회 오류");
  });
}

/* ================================
      질문 등록
================================ */
function registerQna() {
  var teacher = $("#mentorSelect").val();
  var question = $("#qnaQuestion").val().trim();

  if (!teacher) return alert("멘토를 선택해주세요.");
  if (!question) return alert("질문을 입력해주세요.");

  QnaAPI.create({
    student_mb_id: mb_id,
    teacher_mb_id: teacher,
    title: question.substring(0, 20),
    question: question
  }).then(function() {
    alert("질의가 등록되었습니다.");
    location.reload();
  }).fail(function(err) {
    alert(err?.data || "등록 실패");
  });
}
</script>

<?php include_once('../tail.php'); ?>
