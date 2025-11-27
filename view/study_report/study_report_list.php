<?
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "학습보고서";
include_once('../head.php');
?>
<div class="common-section">
  <div class="common-form-box">

    <!-- 1줄차 -->
    <div class="common-form-row first-row">
      <div class="common-select-box">
        <select id="subject" class="common-select">
          <option value="">과목선택</option>
        </select>
        <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png" class="common-select-arrow">
      </div>

      <button class="common-form-btn" onclick="searchReport()">검색</button>
    </div>

    <!-- 2줄차 -->
    <div class="common-form-row second-row">
      <span class="common-form-label">시험일</span>

      <input type="date" id="start_date" class="common-input-date">
      <input type="date" id="end_date" class="common-input-date">
    </div>

  </div>


  <!-- 📄 리스트 -->
  <div class="common-list-container" id="reportList"></div>

  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>
</div>

<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>
<script src="<?= G5_API_URL ?>/api_study_report.js"></script>

<script>
  var page = 1;
  var loading = false;

  /* -------------------------
      페이지 진입 시 과목 목록 로딩
     ------------------------- */
  loadSubjects();

  function loadSubjects() {
    apiMockSubject.list(1, 200, {})
      .then(function(res) {
        var list = res.data.list || [];

        list.forEach(function(m) {
          var label = (m.type ?? '') + ' ' + (m.subject_name ?? '');
          $("#subject").append(`<option value="${m.id}">${label}</option>`);
        });
      })
      .fail(function() {
        alert("과목 목록을 불러오지 못했습니다.");
      });
  }

  /* 최초 리스트 로딩 */
  loadReports();

  /* 검색 클릭 */
  function searchReport() {
    page = 1;
    $("#reportList").empty();
    loadReports();
  }

  /* -------------------------
      학습보고서 리스트 로딩
     ------------------------- */
  function loadReports() {
    if (loading) return;
    loading = true;

    var filters = {
      subject_id: $("#subject").val(),
      date_from: $("#start_date").val(),
      date_to: $("#end_date").val()
    };

    StudyReportAPI.myList(page, 10, filters)
      .then(function(res) {
        var list = res.data.list || [];
        var total = res.data.total;
        var pageN = res.data.page;
        var num = res.data.num;

        if (list.length === 0 && pageN === 1) {
          $("#reportList").append('<p style="color:#aaa; padding:20px;">등록된 학습보고서가 없습니다.</p>');
          $("#moreWrap").hide();
          loading = false;
          return;
        }

        list.forEach(function(r) {
          $("#reportList").append(makeReportItem(r));
        });

        var hasMore = (pageN * num < total);
        if (hasMore) $("#moreWrap").show();
        else $("#moreWrap").hide();

        loading = false;
      })
      .fail(function() {
        alert("조회 중 오류가 발생했습니다.");
        loading = false;
      });
  }

  /* 더보기 */
  function loadMore() {
    page++;
    loadReports();
  }


  /* -------------------------
       리스트 아이템 생성
     ------------------------- */
  function makeReportItem(r) {
    var subjectLabel = ((r.type ?? '') + ' ' + (r.subject_name ?? '')).trim();
    return `
    <div class="common-item">

      <div class="common-item-row" onclick="toggleDetail(${r.id})">

        <div class="common-info">
          <div class="common-title">${r.title}</div>

          <div class="common-meta">
            과목: ${subjectLabel ?? '-'}<br>
            시험일시: ${r.report_date ?? '-'}
          </div>
        </div>

        <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
            class="common-arrow"
            id="arrow-${r.id}">
      </div>

      <div class="common-detail" id="detail-${r.id}">
        <div class="common-desc">

          <!-- 1) content -->
          <div class="report-content" style="white-space:pre-line; margin-bottom:12px;">
            ${r.content ?? ''}
          </div>

          <!-- 2) 다운로드 링크 (우측 정렬) -->
          ${
            r.result_image
              ? `
                <div style="text-align:right; margin-bottom:12px;">
                  <a href="${r.result_image}" download style="color:#4ea1ff; font-size:14px;">
                    다운로드
                  </a>
                </div>
              `
              : ''
          }

          <!-- 3) 이미지 -->
          ${
            r.result_image
              ? `<img src="${r.result_image}" class="report-result-img" style="width:100%; border-radius:8px;">`
              : `<div style="padding:20px; text-align:center; color:#aaa;">결과 이미지 없음</div>`
          }

        </div>
      </div>

    </div>
    `;
  }



  /* 펼침/접기 토글 */
  function toggleDetail(id) {
    $("#detail-" + id).toggleClass("open");
    $("#arrow-" + id).toggleClass("open");
  }
</script>

<? include_once('../tail.php'); ?>