<?
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "수업영상";
include_once('../head.php');
?>


<div class="common-list-container" id="videoList"></div>

<div class="common-more-wrap" id="moreWrap" style="display:none;">
  <button class="common-more-btn" onclick="loadMore()">더보기</button>
</div>

<script src="<?= G5_API_URL ?>/api_video.js"></script>

<script>
  var page = 1;
  var loading = false;

  /* 최초 로딩 */
  loadVideos();

  function loadVideos() {
    if (loading) return;
    loading = true;

    VideoAPI.list(page, 10).then(function(res) {
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
      // console.log(res.page + " # " + res.num + " # "  + res.total)
      if (hasMore) {
        $("#moreWrap").show();
      } else {
        $("#moreWrap").hide();
      }

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
</script>

<? include_once('../tail.php'); ?>