<?php
include_once('./_common.php');
$menu_group = 'consult';
$g5['title'] = "비대면 질의응답";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");

// 교사 role 체크(선택 사항)
// if ($member['role'] !== 'TEACHER') alert("권한이 없습니다.");
?>

<div class="wrap">

<!-- 리스트 -->
<div class="common-list-container" id="qnaList"></div>

<!-- 더보기 버튼 -->
<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>
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
        teacher_mb_id: mb_id // 교사용: 나에게 들어온 질문만 조회
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
      var answer = row.answer ?? '';
      var status = row.status ?? '';
      var teacher = row.teacher_name ?? '';
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

      if (row.files && row.files.length > 0) {

        row.files.forEach(function(f) {

          var fileUrl = f.file_url;

          // 이미지 미리보기
          if (f.is_image) {
            html += `
              <div style="margin-top:12px;">
                <img src="${fileUrl}" style="max-width:100%;border-radius:6px;">
              </div>
            `;
          }

          // 다운로드 버튼
          html += `
            <div style="margin-top:10px; margin-bottom:10px;">
              <button class="common-btn"
                style="padding:6px 12px;font-size:14px;width:auto;height:auto;"
                onclick="downloadFile('${fileUrl}', '${f.file_name}')">
                첨부파일 다운로드
              </button>
              <button class="common-btn"
                style="padding:6px 10px;font-size:13px;width:auto;height:auto;"
                onclick="deleteFile(${row.id}, ${f.bf_no})">
                첨부파일 삭제
              </button>

            </div>
          `;
        });
      }

      var btnLabel = (status === '답변완료') ? '답변 수정' : '답변 등록';
      var prevAnswer = answer ?? '';
      /* 답변 작성 textarea */
      html += `
      <textarea id="answer-${id}" class="common-input"
        style="height:120px;padding-top:12px;margin-bottom:10px;"
        placeholder="답변을 입력하세요.">${prevAnswer}</textarea>

      <input type="file" id="file-${id}" accept="image/*,.pdf,.doc,.docx"
        style="margin-bottom:10px;color:#E7E3DCE0;">

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
    var fileObj = $("#file-" + id)[0].files[0];

    if (!answer) {
      alert("답변을 입력해주세요.");
      return;
    }

    var fd = new FormData();
    fd.append("type", "QNA_ANSWER_FILE_UPLOAD");
    fd.append("id", id);
    fd.append("teacher_mb_id", mb_id);
    fd.append("answer", answer);

    if (fileObj) fd.append("file", fileObj);

    $.ajax({
      url: g5_ctrl_url + "/ctrl_qna.php",
      method: "POST",
      data: fd,
      processData: false,
      contentType: false,
      dataType: "json"
    }).then(function(res) {
      if (res.result === "SUCCESS") {
        alert("답변이 등록되었습니다.");
        location.reload();
      } else {
        alert("등록 실패");
      }
    }).fail(function(err) {
      alert(err?.data || "등록 실패");
    });
  }


  async function downloadFile(url, name) {
    const ok = await appConfirm('다운로드 받으시겠습니까?');

    if (ok) {
      var a = document.createElement("a");
      a.href = url;
      a.download = name || "download";
      document.body.appendChild(a);
      a.click();
      a.remove();
    }
  }

  async function deleteFile(qna_id, bf_no) {

    const ok = await appConfirm("정말 이 첨부파일을 삭제하시겠습니까?");
    if (!ok) return;

    QnaAPI.deleteFile(qna_id, bf_no)
      .then(function(res) {
        alert("첨부파일이 삭제되었습니다.");

        // 🔥 삭제 후 상세 내용을 다시 새로 불러오기
        // 슬라이드 닫고 다시 열기 (UI 갱신)
        var box = $("#detail-" + qna_id);
        box.slideUp(0);
        toggleDetail(qna_id);

      })
      .fail(function(err) {
        alert(err?.data || "삭제 실패");
      });
  }
</script>

<?php include_once('../tail.php'); ?>