<?
$sub_menu = '010200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');

$g5['title'] = '교사등록';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

// 파람
$w     = $_REQUEST['w'] ?? '';
$mb_id = $_REQUEST['mb_id'] ?? '';  // 교사도 mb_id 기준

$defaults = get_member_form_defaults();  // 학생과 동일한 기본값 사용
$db_row   = [];

// 등록모드
if ($w === '' || $w === 'w') {

    $row = $defaults;

} else if ($w === 'u' && $mb_id !== '') {

    $db_row = select_member_one_by_id($mb_id);
    if (!$db_row) alert("교사 정보를 찾을 수 없습니다.");

    $row = array_merge($defaults, $db_row);

} else {
    alert("잘못된 요청입니다.");
}

?>

<script src="<?= G5_API_URL ?>/api_member.js"></script>

<form name="t_form" id="t_form" method="post" autocomplete="off">
  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="mb_id" value="<?= $mb_id ?>">
  <input type="hidden" name="role" value="TEACHER">

  <div class="tbl_frm01 tbl_wrap local_sch04">
    <table>
      <caption><?= $g5['title'] ?></caption>

      <tbody>

        <tr>
          <th class="required">이름</th>
          <td><input type="text" name="mb_name" class="frm_input" value="<?= $row['mb_name'] ?>"></td>

          <th class="required">전화번호</th>
          <td><input type="text" name="mb_hp" class="frm_input" value="<?= $row['mb_hp'] ?>"></td>
        </tr>

        <tr>
          <th>이메일</th>
          <td><input type="text" name="mb_email" class="frm_input" value="<?= $row['mb_email'] ?>"></td>

          <th>주소</th>
          <td><input type="text" name="mb_addr1" class="frm_input" value="<?= $row['mb_addr1'] ?>"></td>
        </tr>

        <tr>
          <th class="required">인증번호</th>
          <td colspan="3">
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="text" name="auth_no" class="frm_input" value="<?= $row['auth_no'] ?>" placeholder="8자리" style="width:200px;">

              <?php if ($w === 'u') { ?>
                <button type="button" class="btn btn_01">문자발송</button>
              <?php } else { ?>
                <button type="button" class="btn btn_01" disabled style="opacity:0.5; cursor:not-allowed;">문자발송</button>
              <?php } ?>

              <span style="color:#777; font-size:12px;">
                문자발송은 교사등록 시 자동으로 발송됩니다.
              </span>
            </div>
          </td>
        </tr>

        <tr>
          <th>입사일</th>
          <td><input type="date" name="join_date" class="frm_input" value="<?= $row['join_date'] ?>"></td>

          <th>퇴사일</th>
          <td><input type="date" name="out_date" class="frm_input" value="<?= $row['out_date'] ?>"></td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./member_list.php?mode=teacher" class="btn btn_02">목록</a>
    <button type="button" class="btn_submit btn" onclick="saveTeacher()">저장</button>
  </div>

</form>

<script>

  function saveTeacher() {

    if (!validateTeacherForm()) return;

    let data = $("#t_form").serialize();
    let w = $("input[name='w']").val();

    // 중복 체크
    $.post(
      g5_ctrl_url + "/ctrl_member.php",
      data + "&type=MEMBER_CHECK_DUP",
      function(res) {
        if (res.data.duplicate) {
          alert("동일 이름/전화번호 교사가 이미 존재합니다.");
          return;
        }

        // INSERT / UPDATE 분기
        if (w === 'u') {
          memberAPI.update(data).then(afterSave);
        } else {
          memberAPI.create(data).then(afterSave);
        }
      },
      'json'
    );
  }

  function afterSave(res) {
    if (res.result === 'SUCCESS') {
      alert('저장되었습니다.');
      location.href = './member_list.php?mode=teacher';
    } else {
      alert('저장 실패: ' + (res.data || '오류'));
    }
  }

  function validateTeacherForm() {
    if ($("input[name='mb_name']").val().trim() === '') {
      alert("이름은 필수 항목입니다.");
      return false;
    }
    if ($("input[name='mb_hp']").val().trim() === '') {
      alert("전화번호는 필수 항목입니다.");
      return false;
    }
    if ($("input[name='auth_no']").val().trim() === '') {
      alert("인증번호는 필수 항목입니다.");
      return false;
    }
    return true;
  }

</script>

<?
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>
