<?php
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "모의고사 신청";
include_once('../head.php');
?>

<div class="wrap">

  <div class="common-list-container" id="mockApplyContainer"></div>

  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>

</div>

<script src="<?= G5_API_URL ?>/api_mock_apply.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_test.js"></script>

<script>

  var page = 1;
  var rows = 5;
  var loading = false;

  $(function() {
    loadMockApplyList(); // 첫 로딩
  });

  /* -------------------------------------------------------------
   * overviewList 를 이용한 페이징 처리
   * ------------------------------------------------------------- */
  function loadMockApplyList() {

    if (loading) return;
    loading = true;

    apiMockApply.overviewList(page, rows)
      .then(function(res) {

        var list  = res.data.list || [];
        var total = res.data.total;
        var p     = res.data.page;
        var r     = res.data.rows;

        // 첫 페이지는 리셋
        if (p === 1) {
          $("#mockApplyContainer").html("");
        }

        // 기존 renderMockApply 구조 유지 → 아이템별 렌더링만 append
        list.forEach(function(item) {
          $("#mockApplyContainer").append(renderMockApplyItem(item));
        });

        // 더보기 표시 여부
        var hasMore = (p * r < total);
        if (hasMore) {
          $("#moreWrap").show();
        } else {
          $("#moreWrap").hide();
        }

        loading = false;
      })
      .fail(function() {
        alert("모의고사 정보를 불러오지 못했습니다.");
        loading = false;
      });
  }

  /* 더보기 버튼 */
  function loadMore() {
    page++;
    loadMockApplyList();
  }

  /* =============================================================
   * 기존 renderMockApply()는 목록 전체를 다시 그리는 함수 → 유지
   * → 페이징에서는 "아이템 1개"만 생성하는 함수가 필요
   * ============================================================= */
  function renderMockApplyItem(item) {

    var mock = item.mock;
    var subjects = item.subjects;
    var canApply = item.can_apply;

    var statusLabel = getStatusLabel(mock);
    var statusClass = (statusLabel === '접수중') ? 'gold' : 'gray';

    var html = `
      <div class="mock-item-box">

        <div class="mock-title-row" onclick="toggleDetail(${mock.id})">
            <div class="common-title">${mock.name}</div>
            <span class="mock-status-badge ${statusClass}">
                ${statusLabel}
            </span>
        </div>

        <div class="mock-meta">
            신청기간 ${mock.apply_start} ~ ${mock.apply_end}<br>
            시험일시 ${mock.exam_date}<br>
            등록일시 ${mock.reg_dt}
        </div>

        <div style="text-align:right; margin-top:6px;">
            <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
                 class="common-arrow"
                 id="arrow-${mock.id}"
                 onclick="toggleDetail(${mock.id}); event.stopPropagation();">
        </div>

        <div id="detail-${mock.id}" class="mock-subject-list" style="display:none;">
    `;

    subjects.forEach(function(sub) {

      var active = sub.applied ? "active" : "";
      var disabled = canApply ? "" : "disabled";

      html += `
        <div class="mock-subject-row" onclick="event.stopPropagation();">
            <div class="mock-subject-name">${sub.subject_name}</div>

            <div class="mock-switch ${active} ${disabled}"
                 onclick="toggleApply(${mock.id}, ${sub.id}, this); event.stopPropagation();">
            </div>
        </div>
      `;
    });

    html += `
        </div>
      </div>
    `;

    return html;
  }

  /* =============================================================
   * 기존 기능 유지 (절대 수정 금지)
   * ============================================================= */

  function getStatusLabel(mock) {
    const now = new Date();

    if (mock.status === "접수중") return "접수중";
    if (mock.apply_start && now < new Date(mock.apply_start)) return "접수전";
    if (mock.apply_end && now > new Date(mock.apply_end)) return "접수완료";

    return mock.status;
  }

  function toggleDetail(id) {
    var box = $("#detail-" + id);
    var arrow = $("#arrow-" + id);

    if (box.is(":visible")) {
      box.slideUp(180);
      arrow.removeClass("open");
    } else {
      box.slideDown(200);
      arrow.addClass("open");
    }
  }

  function toggleApply(mock_id, subject_id, el) {
    var $el = $(el);

    if ($el.hasClass("disabled")) return;

    apiMockApply.toggle(mock_id, subject_id)
      .then(function() {
        $el.toggleClass("active");
      })
      .fail(function() {
        alert("신청 처리 중 오류가 발생했습니다.");
      });
  }

</script>

<?php include_once('../tail.php'); ?>
