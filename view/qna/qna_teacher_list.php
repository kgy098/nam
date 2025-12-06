<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "비대면 질의응답 (교사)";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");

// 교사 role 체크(선택 사항)
// if ($member['role'] !== 'TEACHER') alert("권한이 없습니다.");
?>


<!-- 리스트 -->
<div class="common-list-container" id="qnaList"></div>

<!-- 더보기 버튼 -->
<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>

<script src="<?= G5_API_URL ?>/api_qna.js"></script>

<script>
var mb_id = "<?= $mb_id ?>";
var page = 1;
var loading = false;

/* ================================
      최초 로드
================================ */
$(document).ready(function() {
  loadQnaList();
});

/* ================================
      리스트 불러오기
================================ */
function loadQnaList() {
  if (loading) return;
  loading = true;

  QnaAPI.list({
    page: page,
    rows: 10,
    teacher_mb_id: mb_id   // 교사용: 나에게 들어온 질문만 조회
  })
  .then(function(res) {
    renderQnaList(res.data.list || []);

    if (res.data.total > page * 10) $("#moreWrap").show();
    else $("#moreWrap").hide();

    loading = false;
  })
  .fail(function() {
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
    var id     = row.id;
    var title  = row.title ?? '';
    var status = row.status ?? '';
    var regdt  = (row.reg_dt ?? '').substring(0, 10).replace(/-/g, '.');

    var badge = `
      <span class="mock-status-badge ${status === '답변완료' ? 'gold' : 'gray'}">
        ${status}
      </span>
    `;

    var html = `
      <div class="mock-item-box">

        <!-- 제목행 -->
        <div class="mock-title-row" onclick="toggleDetail(${id})">
          <div class="qna-title">${title}</div>
          ${badge}
        </div>

        <!-- 날짜 -->
        <div class="mock-meta">${regdt}</div>

        <!-- Arrow -->
        <div style="text-align:right;margin-top:6px;">
          <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
               class="common-arrow"
               id="arrow-${id}"
               onclick="toggleDetail(${id});event.stopPropagation();">
        </div>

        <!-- 상세(답변쓰기 포함) -->
        <div id="detail-${id}" class="mock-subject-list" style="display:none;"></div>
      </div>
    `;

    wrap.append(html);
  });

  if (page === 1 && list.length === 0) {
    wrap.html(`
      <div style="padding:40px 0;text-align:center;color:#ADABA6E0;font-size:15px;">
        결과가 없습니다.
      </div>
    `);
    return;
  }
}

/* ================================
      상세 펼침 - 교사 버전
================================ */
function toggleDetail(id) {
  var box = $("#detail-" + id);
  var arrow = $("#arrow-" + id);

  if (box.is(":visible")) {
    box.slideUp(150);
    arrow.removeClass("open");
    return;
  }

  // 상세 조회
  QnaAPI.get(id).then(function(res) {
    var row = res.data || {};

    var question = row.question ?? '';
    var answer   = row.answer ?? '';
    var status   = row.status ?? '';
    var teacher  = row.teacher_name ?? '';
    var answeredDt = (row.answered_dt ?? '').substring(0, 10).replace(/-/g, '.');

    var html = `
      <div class="qna-detail-box">

        <!-- 학생 질문 -->
        <div class="qna-question-view"
             style="margin-bottom:12px;color:#E7E3DCE0;line-height:1.5;">
          ${question.replace(/\n/g, '<br>')}
        </div>
    `;

    /* 기존 답변 표시 */
    if (status === '답변완료') {
      html += `
        <div class="qna-answer-box" style="margin-bottom:15px;">
          <div class="qna-answer-header">
            <span>↳ ${teacher} 선생님</span>
            <span>${answeredDt}</span>
          </div>
          <div style="white-space:pre-line;">${answer}</div>
        </div>
      `;
    }

    var btnLabel = (status === '답변완료') ? '답변 수정' : '답변 등록';
    var prevAnswer = answer ?? '';
    /* 답변 작성 textarea */
    html += `
      <textarea id="answer-${id}" class="common-input"
        style="height:120px;padding-top:12px;margin-bottom:10px;"
        placeholder="답변을 입력하세요.">${prevAnswer}</textarea>

      <button class="common-btn" onclick="submitAnswer(${id})">
        ${btnLabel}
      </button>

      </div>
    `;

    box.html(html);
    box.slideDown(150);
    arrow.addClass('open');

  }).fail(function() {
    alert("상세 조회 오류");
  });
}

/* ================================
      답변 등록
================================ */
function submitAnswer(id) {
  var answer = $("#answer-" + id).val().trim();
  if (!answer) {
    alert("답변을 입력해주세요.");
    return;
  }

  QnaAPI.answer(id, {
    teacher_mb_id: mb_id,
    answer: answer
  })
  .then(function() {
    alert("답변이 등록되었습니다.");
    location.reload();
  })
  .fail(function(err) {
    alert(err?.data || "등록 실패");
  });
}
</script>

<?php include_once('../tail.php'); ?>
