<?php
include_once('./_common.php');
$menu_group = 'consult'; // 하단 네비게이션 "상담&예약" 활성화를 위해
$g5['title'] = "비대면 질의응답";
include_once('../head.php');

$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') {
  alert("로그인이 필요합니다.");
}
?>

<style>
/* ============================================
   QNA 전용 스타일 (공통 스타일 최소 확장)
   Prefix: common-qna-
============================================ */

/* 질문 카드 */
.common-qna-card {
  background: #26262d;
  border: 1px solid #323238;
  border-radius: 12px;
  padding: 18px 16px;
  margin-bottom: 16px;
  box-shadow: 0 0 20px rgba(0,0,0,0.15);
  cursor: pointer;
}

/* 상태 배지 */
.common-qna-badge {
  display: inline-block;
  padding: 4px 10px;
  font-size: 12px;
  border-radius: 6px;
  margin-bottom: 10px;
}

.common-qna-badge.gray {
  background: rgba(255,255,255,0.08);
  color: #A0A0A0;
}

.common-qna-badge.gold {
  background: rgba(228,201,146,0.15);
  color: #E4C992E0;
}

/* 질문 제목 */
.common-qna-title {
  font-size: 16px;
  font-weight: 600;
  color: #ffffffd0;
  margin-bottom: 8px;
}

/* 날짜 */
.common-qna-date {
  font-size: 14px;
  color: #ADABA6E0;
}

/* 펼침 영역(답변) */
.common-qna-answer-box {
  background: #2A2A31;
  border: 1px solid #3A3A3F;
  padding: 14px 12px;
  border-radius: 8px;
  margin-top: 14px;
  color: #D9D8D5E0;
}

.common-qna-answer-header {
  display: flex;
  justify-content: space-between;
  font-size: 14px;
  margin-bottom: 8px;
  color: #D9D8D5E0;
}

.common-qna-answer-content {
  font-size: 14px;
  line-height: 1.5em;
  color: #D9D8D5E0;
}

/* 멘토 선택 bottom sheet */
.common-qna-sheet-dim {
  display: none;
  position: fixed;
  inset: 0;
  background: rgba(0,0,0,0.45);
  z-index: 900;
}

.common-qna-sheet-box {
  display: none;
  position: fixed;
  left: 0;
  bottom: -70%;
  width: 100%;
  max-height: 70%;
  background: #1B1B20;
  border-radius: 14px 14px 0 0;
  padding: 20px;
  z-index: 999;
  transition: bottom 0.25s ease;
}

.common-qna-sheet-title {
  text-align: center;
  font-size: 16px;
  font-weight: 600;
  color: #E5C784;
  margin-bottom: 18px;
}

.common-qna-mentor-list div {
  padding: 14px;
  border-radius: 8px;
  background: #26262d;
  border: 1px solid #323238;
  margin-bottom: 10px;
  font-size: 15px;
  color: #fff;
  text-align: center;
  cursor: pointer;
}

.common-qna-sheet-btn-wrap {
  margin-top: 16px;
  display: flex;
  gap: 12px;
}

.common-qna-btn {
  flex: 1;
  height: 46px;
  border-radius: 8px;
  background: #2A2A31;
  border: 1px solid #3A3A3F;
  font-size: 15px;
  color: #D9D8D5E0;
}

.common-qna-btn.confirm {
  background: #A17E36;
  border: none;
  color: #000;
  font-weight: 600;
}
</style>


<!-- ===============================
      내 질문 목록
=============================== -->
<div class="common-list-container" id="qnaList"></div>

<!-- 더보기 -->
<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>


<!-- ===============================
      멘토 질의하기 영역
=============================== -->
<div style="width:90%;max-width:420px;margin:30px auto 10px;font-size:17px;font-weight:600;color:#ffffffd0;">
  멘토 질의하기
</div>

<div class="common-section">

  <!-- 멘토 선택 -->
  <div class="common-form-row first-row">
    <div class="common-select-box" onclick="openMentorSheet()">
      <select class="common-select" disabled>
        <option id="mentorSelectLabel">멘토 선택</option>
      </select>
      <img class="common-select-arrow" src="<?= G5_THEME_IMG_URL ?>/nam/ico/arrow_down.png">
    </div>
  </div>

  <!-- 질문 입력 -->
  <div class="common-form-row">
    <textarea id="qnaQuestion" class="common-input" style="height:120px;padding-top:12px;"
      placeholder="질의를 입력하세요."></textarea>
  </div>

  <!-- 등록 버튼 -->
  <div class="common-form-row">
    <button class="common-btn" onclick="registerQna()">질의 등록</button>
  </div>

</div>


<!-- ===============================
      멘토 선택 bottom sheet
=============================== -->
<div class="common-qna-sheet-dim" id="qnaSheetDim" onclick="closeMentorSheet()"></div>

<div class="common-qna-sheet-box" id="qnaSheetBox">
  <div class="common-qna-sheet-title">멘토 선택</div>

  <div id="mentorListBox" class="common-qna-mentor-list"></div>

  <div class="common-qna-sheet-btn-wrap">
    <button class="common-qna-btn" onclick="closeMentorSheet()">취소</button>
    <button class="common-qna-btn confirm" onclick="confirmMentor()">확인</button>
  </div>
</div>


<script src="<?= G5_API_URL ?>/api_qna.js"></script>

<script>
var mb_id = "<?= $mb_id ?>";

var page = 1;
var loading = false;
var selectedMentor = '';
var mentorNameMap = {}; // mb_id -> mb_name

$(document).ready(function() {
  loadQnaList();

  // 멘토 목록 로딩
  QnaAPI.list({ type: 'MENTOR_LIST' }); // 필요 시 추가 구현
});

/* ================================
      QNA 목록 로딩
================================ */
function loadQnaList() {
  if (loading) return;
  loading = true;

  QnaAPI.list({ page: page, rows: 10 }).then(function(res) {
    var list = res.data.list || [];
    renderQnaList(list);

    if (res.data.total > page * 10) {
      $('#moreWrap').show();
    } else {
      $('#moreWrap').hide();
    }

  }).fail(function() {
    alert('목록을 불러오는 중 오류 발생');
  }).always(function() {
    loading = false;
  });
}

function loadMore() {
  page++;
  loadQnaList();
}

/* ================================
      목록 렌더링
================================ */
function renderQnaList(list) {
  var html = '';

  list.forEach(function(row) {
    var id = row.id;
    var title = row.title || '';
    var status = row.status || '';
    var regdt = (row.reg_dt || '').substring(0,10).replace(/-/g,'.');

    var badge = (status === '답변완료')
      ? '<div class="common-qna-badge gold">답변완료</div>'
      : '<div class="common-qna-badge gray">미답변</div>';

    html += `
      <div class="common-qna-card" data-id="${id}">
        ${badge}
        <div class="common-qna-title">${title}</div>
        <div class="common-qna-date">${regdt}</div>
        <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/arrow_down.png"
             class="common-arrow" style="margin-top:10px;">
        <div class="common-detail common-qna-detail" style="display:none;"></div>
      </div>
    `;
  });

  $('#qnaList').append(html);

  // 클릭 이벤트(펼침)
  $('#qnaList .common-qna-card').each(function() {
    var $card = $(this);
    var id = $card.data('id');

    $card.find('.common-arrow').off('click').on('click', function(e) {
      e.stopPropagation();
      toggleDetail($card, id);
    });
  });
}

/* ================================
      상세 펼침
================================ */
function toggleDetail($card, id) {
  var $detail = $card.find('.common-qna-detail');
  var $arrow = $card.find('.common-arrow');

  if ($detail.is(':visible')) {
    $detail.slideUp(150);
    $arrow.removeClass('open');
    return;
  }

  // 닫혀 있던 경우 → Ajax로 상세 조회
  QnaAPI.get(id).then(function(res) {
    var row = res.data;
    var teacher = row.teacher_mb_id || '';
    var answeredDt = (row.answered_dt || '').substring(0,10).replace(/-/g,'.');
    var answer = row.answer || '';

    var html = '';

    if (row.status === '답변완료') {
      html = `
        <div class="common-qna-answer-box">
          <div class="common-qna-answer-header">
            <span>↳ ${teacher} 선생님</span>
            <span>${answeredDt}</span>
          </div>
          <div class="common-qna-answer-content">${answer}</div>
        </div>
      `;
    }

    $detail.html(html);
    $detail.slideDown(150);
    $arrow.addClass('open');

  }).fail(function() {
    alert('상세 조회 중 오류 발생');
  });
}


/* ================================
      멘토 bottom sheet
================================ */
function openMentorSheet() {
  // 멘토 목록 불러오기
  QnaAPI.list({ type: 'MENTOR_LIST' }); // ctrl에서 구현 시
  // 여기서는 데모 데이터로 처리 가능
  var demo = [
    { mb_id: 't1', mb_name: '박길동' },
    { mb_id: 't2', mb_name: '김정희' },
    { mb_id: 't3', mb_name: '최순이' }
  ];

  mentorNameMap = {};
  var html = '';
  demo.forEach(function(t) {
    mentorNameMap[t.mb_id] = t.mb_name;
    html += `<div data-id="${t.mb_id}">${t.mb_name}</div>`;
  });

  $('#mentorListBox').html(html);

  $('#qnaSheetDim').show();
  $('#qnaSheetBox').show().css('bottom','0');

  $('#mentorListBox div').on('click', function() {
    $('#mentorListBox div').css('opacity','0.5');
    $(this).css('opacity','1');
    selectedMentor = $(this).data('id');
  });
}

function closeMentorSheet() {
  $('#qnaSheetBox').css('bottom','-70%');
  setTimeout(function(){ $('#qnaSheetBox').hide(); }, 250);
  $('#qnaSheetDim').hide();
}

function confirmMentor() {
  if (!selectedMentor) {
    alert('멘토를 선택해주세요.');
    return;
  }
  $('#mentorSelectLabel').text(mentorNameMap[selectedMentor]);
  closeMentorSheet();
}


/* ================================
      질문 등록
================================ */
function registerQna() {
  var question = $('#qnaQuestion').val().trim();

  if (!selectedMentor) {
    alert('멘토를 선택하세요.');
    return;
  }
  if (!question) {
    alert('질문을 입력하세요.');
    return;
  }

  QnaAPI.create({
    student_mb_id: mb_id,
    teacher_mb_id: selectedMentor,
    title: question.substring(0,20),
    question: question
  }).then(function() {
    alert('질의가 등록되었습니다.');
    location.reload();
  }).fail(function(err) {
    alert((err && err.data) || '등록 중 오류 발생');
  });
}
</script>

<?php include_once('../tail.php'); ?>
