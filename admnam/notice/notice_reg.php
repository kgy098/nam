<?php
include_once('./_common.php');

$sub_menu = "020200";
auth_check_menu($auth, $sub_menu, 'w');

include_once(G5_EDITOR_LIB); // 그누보드 기본 에디터 로드

// g5_board_file용 bo_table 고정
$bo_table = 'notice';

// 파라미터
$w  = $_GET['w'] ?? '';     // '' : 등록, 'u' : 수정
$id = (int)($_GET['id'] ?? 0);

$row      = [];
$file_row = [];

if ($w === 'u') {
  // 공지 기본 정보
  $row = select_notice_one($id);
  if (!$row) {
    alert('자료가 존재하지 않습니다.');
  }
  // error_log(__FILE__ . __LINE__ . "\n row: " . print_r($row, true));

  // g5_board_file CRUD 사용 (단일 파일 조회)
  $file_row = get_board_file($bo_table, $id, 0);
}

$page_title  = ($w === 'u') ? '공지사항 수정' : '공지사항 등록';
$g5['title'] = $page_title;

include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_notice.js"></script>

<form name="fnotice" id="fnotice" method="post" enctype="multipart/form-data" autocomplete="off">

  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="id" value="<?= $id ?>">
  <input type="hidden" name="bo_table" value="<?= $bo_table ?>">

  <div class="tbl_frm01 tbl_wrap">
    <table>
      <caption><?= $page_title ?></caption>
      <colgroup>
        <col width="15%">
        <col width="35%">
        <col width="15%">
        <col width="35%">
      </colgroup>
      <tbody>

        <tr>
          <th scope="row">작성자</th>
          <td>
            <input type="text" name="writer_name" class="frm_input"
              value="<?= $row['writer_name'] ?? $member['mb_name'] ?>" readonly>
            <input type="hidden" name="mb_id"
              value="<?= $row['mb_id'] ?? $member['mb_id'] ?>">
          </td>

          <th scope="row">등록일</th>
          <td>
            <input type="text" class="frm_input"
              value="<?= $row['reg_dt'] ?? date('Y-m-d H:i:s') ?>" readonly>
          </td>
        </tr>

        <tr>
          <th scope="row">제목</th>
          <td colspan="3">
            <input type="text" name="title" class="frm_input" style="width:90%;"
              value="<?= $row['title'] ?? '' ?>">
          </td>
        </tr>

        <tr>
          <th scope="row">첨부파일</th>
          <td colspan="3">
            <?php if ($w === 'u' && !empty($file_row['bf_file'])) { ?>
              <div style="margin-bottom:5px;">
                현재 파일:
                <a href="<?= G5_DATA_URL ?>/file/<?= $bo_table ?>/<?= $file_row['bf_file'] ?>"
                  download="<?= get_text($file_row['bf_source']) ?>">
                  <?= get_text($file_row['bf_source']) ?>
                </a>

                <!-- 파일 삭제 체크박스 (선택) -->
                <label style="margin-left:10px;">
                  <input type="checkbox" name="file_del" value="1">
                  파일 삭제
                </label>
              </div>
            <?php } ?>

            <!-- ✅ input 이름은 'file' 단건  -->
            <input type="file" name="file" class="frm_input">
          </td>
        </tr>

        <tr>
          <th scope="row">내용</th>
          <td colspan="3">
            <?php
            $content = '';
            if ($w === 'u' && isset($row['content'])) {
              $content = html_purifier($row['content']);
            }
            echo editor_html('content', $content, 1);
            ?>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./notice_list.php" class="btn btn_02">목록</a>

    <?php if ($w === 'u') { ?>
      <button type="button" class="btn btn_01" id="btnDelete">삭제</button>
    <?php } ?>

    <button type="button" class="btn btn_submit" id="btnSave">
      <?= ($w === 'u') ? '수정' : '저장' ?>
    </button>
  </div>

</form>

<script>
  $(function() {

    $("#btnSave").on("click", function() {
      submitNotice();
    });

    <?php if ($w === 'u') { ?>
      $("#btnDelete").on("click", function() {
        deleteNotice();
      });
    <?php } ?>

  });

  /* 저장 (등록/수정 공통) */
  function submitNotice() {
    // 에디터 값 실제 textarea에 반영
    <?= get_editor_js('content'); ?>

    var form = document.getElementById("fnotice");
    var formData = new FormData(form);

    $.ajax({
      url: './notice_update.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        if (res.result === 'SUCCESS') {
          alert('저장되었습니다.');
          location.href = './notice_list.php';
        } else {
          alert(res.message || '저장 실패');
        }
      },
      error: function(err) {
        console.log(err);
        alert('통신 오류가 발생했습니다.');
      }
    });
  }

  /* 삭제 처리 */
  function deleteNotice() {

    if (!confirm('정말 삭제하시겠습니까?')) return;

    var formData = new FormData();
    formData.append('w', 'd');
    formData.append('id', '<?= $id ?>');
    formData.append('bo_table', '<?= $bo_table ?>');

    $.ajax({
      url: './notice_update.php',
      type: 'POST',
      data: formData,
      processData: false,
      contentType: false,
      dataType: 'json',
      success: function(res) {
        if (res.result === 'SUCCESS') {
          alert('삭제되었습니다.');
          location.href = './notice_list.php';
        } else {
          alert(res.message || '삭제 실패');
        }
      },
      error: function() {
        alert('통신 오류가 발생했습니다.');
      }
    });
  }
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>