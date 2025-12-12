<?
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "수업영상";
include_once('../head.php');

// STUDENT / TEACHER
$role = $member['role'] ?? 'STUDENT';
?>

<div class="wrap">

  <div class="common-list-container" id="videoList"></div>

  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>

  <? if ($role === 'TEACHER') { ?>
    <!-- ============================
     선생님 전용 업로드 영역
    ================================= -->
    <div class="common-section" style="margin-top:20px; padding:16px; background:#1E1E1E; border-radius:8px;">

      <div class="common-row">
        <span class="common-row-label">제목</span>
        <input type="text" id="v_title" class="common-input" placeholder="영상 제목">
      </div>

      <div class="common-row">
        <span class="common-row-label">유튜브ID</span>
        <input type="text" id="v_youtube" class="common-input" placeholder="youtube_id">
      </div>

      <div class="common-row">
        <span class="common-row-label">설명</span>
        <textarea id="v_desc" class="common-input" style="height:120px;padding-top:12px;" placeholder="영상 설명"></textarea>
      </div>

      <div class="common-row" style="margin-top:15px;">
        <button class="common-btn" id="btnUpload">업로드</button>
      </div>

    </div>
  <? } ?>
</div>

<script src="<?= G5_API_URL ?>/api_video.js"></script>

<script>
  var page = 1;
  var loading = false;
  var role = "<?= $role ?>";

  // 아이템 개수 (학생 5개 / 선생님 3개)
  var pageNum = (role === 'TEACHER') ? 3 : 5;

  /* 최초 로딩 */
  loadVideos();

  function loadVideos() {
    if (loading) return;
    loading = true;

    // 공통 API 사용, 단 pageNum만 다르게
    VideoAPI.list(page, pageNum).then(function(res) {
      var list = res.data.list || [];
      var total = res.data.total;
      var page = res.data.page;
      var num = res.data.num;

      if (list.length === 0 && page === 1) {
        $("#videoList").append('<p style="color:#aaa; padding:20px;">등록된 영상이 없습니다.</p>');
        return;
      }

      list.forEach(function(v) {
        $("#videoList").append(makeVideoItem(v));
      });

      var hasMore = (page * num < total);
      if (hasMore) {
        $("#moreWrap").show();
      } else {
        $("#moreWrap").hide();
      }

      loading = false;
    }).fail(function(err) {
      console.error(err);
      loading = false;
    });
  }

  function loadMore() {
    page++;
    loadVideos();
  }

  function makeVideoItem(v) {
    return `
    <div class="common-item">

      <div class="common-item-row" onclick="toggleDetail(${v.id})">
        
        <div class="common-thumb"
             style="
               background-image:url('https://img.youtube.com/vi/${v.youtube_id}/hqdefault.jpg');
               background-size:cover;
               background-position:center;
               background-repeat:no-repeat;
             ">
        </div>

        <div class="common-info">
          <div class="common-title">${v.title}</div>
          <div class="common-meta">${v.reg_dt} | 조회수 ${v.views}</div>
        </div>

        <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
             class="common-arrow"
             id="arrow-${v.id}">
      </div>

      <div class="common-detail" id="detail-${v.id}">
        ${
          v.youtube_id
            ? `<div class="common-youtube">
                  <iframe src="https://www.youtube.com/embed/${v.youtube_id}" allowfullscreen></iframe>
               </div>`
            : ''
        }
        <div class="common-desc">${v.description ?? ''}</div>
      </div>

    </div>
    `;
  }

  function toggleDetail(id) {
    var detail = $("#detail-" + id);
    detail.toggleClass("open");

    var arrow = $("#arrow-" + id);
    arrow.toggleClass("open");
  }


  /* ============================
     선생님 전용 업로드 기능
  ============================= */
  <? if ($role === 'TEACHER') { ?>
    $("#btnUpload").on("click", function() {
      var title = $("#v_title").val().trim();
      var youtube = $("#v_youtube").val().trim();
      var desc = $("#v_desc").val().trim();

      if (!title) return alert("제목을 입력해주세요.");
      if (!youtube) return alert("유튜브ID를 입력해주세요.");

      VideoAPI.add({
          title: title,
          youtube_id: youtube,
          description: desc
        })
        .then(function(res) {
          alert("업로드되었습니다.");
          location.reload();
        })
        .fail(function(err) {
          alert("업로드 실패");
        });
    });
  <? } ?>
</script>

<? include_once('../tail.php'); ?>