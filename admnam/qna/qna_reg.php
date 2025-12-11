<?php
$sub_menu = '030500';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$w  = $_GET['w'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$row = [];

if ($w === 'u') {
  $row = select_qna_one($id);
  if (!$row) alert('자료가 존재하지 않습니다.');
}

// 제목
$g5['title'] = "비대면 질의응답 답변관리";
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

$file_row = get_board_file('qna', $id, 0);
?>

<form id="qna_form" autocomplete="off">
  <input type="hidden" name="id" value="<?= $id ?>">
  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="type" value="QNA_ANSWER">

  <div class="tbl_frm01 tbl_wrap local_sch04">
    <table>
      <caption>비대면 질의응답</caption>
      <colgroup>
        <col width="15%">
        <col width="85%">
      </colgroup>
      <tbody>

        <!-- 제목 -->
        <tr>
          <th scope="row">제목</th>
          <td>
            <input type="text" class="frm_input"
              value="<?= get_text($row['title']) ?>"
              style="width:100%;" readonly>
          </td>
        </tr>

        <!-- 학생명 / 반 -->
        <tr>
          <th scope="row">학생 / 반</th>
          <td>
            <input type="text" class="frm_input"
              value="<?= $row['student_name'] ?> / <?= $row['class_name'] ?>"
              style="width:300px;" readonly>
          </td>
        </tr>

        <!-- 질문 내용 -->
        <tr>
          <th scope="row">질문 내용</th>
          <td>
            <div style="padding:10px; border:1px solid #ddd; min-height:120px; white-space:pre-line;">
              <?= get_text($row['question']) ?>
            </div>
          </td>
        </tr>

        <tr>
          <th scope="row">첨부파일</th>
          <td>
            <?php if ($w === 'u' && !empty($file_row['bf_file'])) { ?>
              <div style="margin-bottom:5px;">
                현재 파일:
                <a href="<?= G5_DATA_URL ?>/file/qna/<?= $file_row['bf_file'] ?>"
                  download="<?= get_text($file_row['bf_source']) ?>">
                  <?= get_text($file_row['bf_source']) ?>
                </a>

                <!-- 파일 삭제 체크박스 -->
                <label style="margin-left:10px;">
                  <input type="checkbox" name="file_del" value="1"> 파일 삭제
                </label>
              </div>
            <?php } ?>

            <!-- 새 파일 업로드 -->
            <input type="file" name="file" class="frm_input">
          </td>
        </tr>


        <!-- 답변 (textarea) -->
        <tr>
          <th scope="row">답변 작성</th>
          <td>
            <textarea id="answer" name="answer"
              style="width:100%; height:200px; padding:10px; resize:vertical;"
              class="frm_input"><?= get_text($row['answer']) ?></textarea>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./qna_list.php" class="btn btn_02">목록</a>
    <button type="button" class="btn btn_submit" id="btnSubmit">
      <?= $row['answer'] ? '답변 수정' : '답변 등록' ?>
    </button>
  </div>
</form>

<script src="<?= G5_API_URL ?>/api_qna.js"></script>

<script>
  $(function() {

    $("#btnSubmit").on("click", function() {

      var answer = $("#answer").val().trim();
      if (!answer) {
        alert("답변 내용을 입력하세요.");
        $("#answer").focus();
        return;
      }

      var form = document.getElementById("qna_form");
      var formData = new FormData(form);

      QnaAPI.submit(formData)
      .done(function(res) {
        alert("저장되었습니다.");
        location.href = "./qna_list.php";
      })
      .fail(function(err) {
        alert(err.message || "저장 실패");
      });

    });

  });
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>