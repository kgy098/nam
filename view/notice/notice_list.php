<?php
include_once('./_common.php');

$menu_group = 'cal';
$g5['title'] = "공지사항";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");
?>

<script src="<?= G5_API_URL ?>/api_notice.js"></script>

<style>
  /* 공지 상세 내용 */
  .notice-content {
    width: 100%;
    margin-top: 10px;
    color: #E7E3DCE0;
    line-height: 1.5em;
    font-size: 14px;
  }

  .notice-content img {
    max-width: 100%;
    height: auto;
    /* display: block; */
  }
</style>

<!-- ================================
     공지사항 리스트
================================ -->
<div class="wrap">
  <div class="common-list-container" id="noticeList"></div>

  <!-- 더보기 -->
  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>
</div>

<script>
  var page = 1;
  var loading = false;

  /* ================================
        최초 로드
  ================================ */
  $(document).ready(function() {
    loadNoticeList();
  });

  /* ================================
        공지 리스트 가져오기
  ================================ */
  function loadNoticeList() {
    if (loading) return;
    loading = true;

    NoticeAPI.list(page, 10)
      .then(function(res) {
        renderNoticeList(res.data.list || []);

        if (res.data.total > page * 10) $("#moreWrap").show();
        else $("#moreWrap").hide();

        loading = false;
      })
      .fail(function() {
        alert("공지사항 조회 오류");
        loading = false;
      });
  }

  function loadMore() {
    page++;
    loadNoticeList();
  }

  /* ================================
        리스트 렌더링
  ================================ */
  function renderNoticeList(list) {
    var wrap = $("#noticeList");

    list.forEach(function(row) {
      var id = row.id;
      var title = row.title ?? '';
      var regdt = (row.reg_dt ?? '').substring(0, 10).replace(/-/g, '.');

      var html = `
      <div class="mock-item-box">

        <!-- 제목 + 날짜 -->
        <div class="mock-title-row" onclick="toggleDetail(${id})">
          <div class="qna-title">${title}</div>
        </div>

        <div class="mock-meta">${regdt}</div>

        <!-- Arrow -->
        <div style="text-align:right;margin-top:6px;">
          <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
               class="common-arrow"
               id="arrow-${id}"
               onclick="toggleDetail(${id});event.stopPropagation();">
        </div>

        <!-- 상세 내용 -->
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

    NoticeAPI.get(id)
      .then(function(res) {
        var row = res.data || {};
        var content = (row.content ?? '').replace(/\n/g, '<br>');

        var fileHtml = '';
        if (row.file_url) {
          fileHtml = `
            <div style="margin-top:12px; text-align:right;">
              <button class="common-form-btn"
                      style="width:auto; padding:6px 10px; font-size:13px;"
                      onclick="downloadNoticeFile('${row.file_url}', '${row.file_name}')">
                파일 다운로드
              </button>
            </div>
          `;
        }

        var html = `
        <div class="notice-content">${content}</div>
        ${fileHtml}
      `;

        box.html(html);
        box.slideDown(150);
        arrow.addClass("open");
      })
      .fail(function() {
        alert("상세 조회 오류");
      });
  }

  async function downloadNoticeFile(url, name) {
    const ok = await appConfirm('다운로드 받으시겠습니까?');
    

    if ( ok ) {
      var a = document.createElement("a");
      a.href = url;
      a.download = name || "download";
      document.body.appendChild(a);
      a.click();
      a.remove();
    }
  }
</script>

<?php include_once('../tail.php'); ?>