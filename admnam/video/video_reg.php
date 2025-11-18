<?php
$sub_menu = '040100';
include_once('./_common.php');
include_once(G5_EDITOR_LIB);

auth_check_menu($auth, $sub_menu, 'w');

$w  = $_REQUEST['w'] ?? '';
$id = (int)($_REQUEST['id'] ?? 0);
$row = [];

if ($w === 'u') {
  $row = select_video_one($id);
  if (!$row) alert('존재하지 않는 영상입니다.');
}

$g5['title'] = ($w == 'u' ? '수업영상 수정' : '수업영상 등록');
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_video.js"></script>

<form id="fvideo" autocomplete="off">

  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="id" value="<?= $id ?>">

  <div class="tbl_frm01 tbl_wrap">
    <table>
      <caption>영상등록</caption>
      <colgroup>
        <col width="15%">
        <col width="85%">
      </colgroup>
      <tbody>

        <tr>
          <th scope="row">제목</th>
          <td>
            <input type="text" name="title" value="<?= $row['title'] ?? '' ?>" class="frm_input" style="width:70%;">
          </td>
        </tr>

        <tr>
          <th scope="row">유튜브 ID</th>
          <td>
            <input type="text" name="youtube_id" value="<?= $row['youtube_id'] ?? '' ?>" class="frm_input" style="width:40%;">
            <div id="ytPreview" style="margin-top:15px;">
              <?php if ($w === 'u' && !empty($row['youtube_id'])) { ?>
                <iframe width="560" height="315"
                  src="https://www.youtube.com/embed/<?= $row['youtube_id'] ?>"
                  frameborder="0"
                  allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                  allowfullscreen>
                </iframe>
              <?php } ?>
            </div>

          </td>
        </tr>

        <tr>
          <th scope="row">내용</th>
          <td>
            <?php
            $content = $row['description'] ?? '';
            echo editor_html('description', $content, 0);
            ?>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <div style="text-align:center; margin:30px 0;">
    <a href="./video_list.php" class="btn btn_02" style="min-width:120px;">목록으로</a>

    <?php if ($w === 'u') { ?>
      <button type="button" id="btnDelete" class="btn btn_02" style="min-width:120px;">삭제</button>
      <button type="button" id="btnSave" class="btn btn_submit" style="min-width:120px;">수정</button>
    <?php } else { ?>
      <button type="button" id="btnSave" class="btn btn_submit" style="min-width:120px;">등록</button>
    <?php } ?>
  </div>

</form>

<script>
  $(function() {

    /* 저장 */
    $("#btnSave").on("click", function() {

      var f = $("#fvideo")[0];

      var data = {
        title: f.title.value,
        youtube_id: f.youtube_id.value,
        description: f.description.value
      };

      if (f.w.value === '') {
        // 신규 등록
        VideoAPI.add(data).then(function() {
          alert("등록되었습니다.");
          location.href = "./video_list.php";
        }).catch(function() {
          alert("등록 실패");
        });

      } else {
        // 수정
        data.id = f.id.value;

        VideoAPI.update(data.id, data).then(function() {
          alert("수정되었습니다.");
          location.href = "./video_list.php";
        }).catch(function() {
          alert("수정 실패");
        });
      }
    });


    /* 삭제 */
    $("#btnDelete").on("click", function() {
      if (!confirm("삭제하시겠습니까?")) return;

      var id = $("input[name=id]").val();

      VideoAPI.remove(id).then(function() {
        alert("삭제되었습니다.");
        location.href = "./video_list.php";
      }).catch(function() {
        alert("삭제 실패");
      });
    });

  });
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>