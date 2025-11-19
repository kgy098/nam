<?
$sub_menu = '010200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = '교사등록';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

// 파람
$w  = $_REQUEST['w'] ?? '';
$no = $_REQUEST['no'] ?? '';

$defaults = [
    'mb_name'   => '',
    'mb_hp'     => '',
    'mb_email'  => '',
    'mb_addr'   => '',
    'auth_no'   => '',
    'join_date' => '',
    'out_date'  => '',
];

// 기존 DB 값 로딩
if ($no) {
    $db_row = select_member_one($no);
}
$row = array_merge($defaults, $db_row ?? []);

if (!$w) $w = "w";
?>

<script src="<?= G5_API_URL ?>/api_member.js"></script>

<form name="t_form" id="t_form" method="post" autocomplete="off">
  <input type="hidden" name="w" value="<?= $w; ?>">
  <input type="hidden" name="no" value="<?= $no; ?>">
  <input type="hidden" name="role" value="TEACHER">

  <div class="tbl_frm01 tbl_wrap local_sch04">
    <table>
      <caption><?= $g5['title']; ?></caption>
      <colgroup>
        <col width="15%">
        <col width="35%">
        <col width="15%">
        <col width="35%">
      </colgroup>

      <tbody>

        <tr>
          <th scope="row">이름</th>
          <td><input type="text" class="frm_input" name="mb_name" value="<?= $row['mb_name']; ?>"></td>

          <th scope="row">전화번호</th>
          <td><input type="text" class="frm_input" name="mb_hp" value="<?= $row['mb_hp']; ?>"></td>
        </tr>

        <tr>
          <th scope="row">이메일</th>
          <td><input type="text" class="frm_input" name="mb_email" value="<?= $row['mb_email']; ?>"></td>

          <th scope="row">주소</th>
          <td><input type="text" class="frm_input" name="mb_addr" value="<?= $row['mb_addr']; ?>"></td>
        </tr>

        <tr>
          <th scope="row">인증번호</th>
          <td colspan="3">
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="text" class="frm_input" name="auth_no"
                     placeholder="숫자 8자리를 입력하세요."
                     value="<?= $row['auth_no']; ?>"
                     style="width:200px;">
              <button type="button" class="btn btn_01">문자발송</button>
            </div>
          </td>
        </tr>

        <tr>
          <th scope="row">입사일</th>
          <td><input type="date" class="frm_input" name="join_date" value="<?= $row['join_date']; ?>"></td>

          <th scope="row">퇴사일</th>
          <td><input type="date" class="frm_input" name="out_date" value="<?= $row['out_date']; ?>"></td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./teacher_list.php" class="btn btn_02">목록</a>
    <input type="submit" value="등록" class="btn_submit btn" onclick="createTeacher();">
  </div>

</form>

<script>
  function createTeacher() {

    var paramStr = $("#t_form").serialize();

    // 중복체크 (이름/전화번호)
    $.post(g5_ctrl_url + '/ctrl_member.php', paramStr + '&type=MEMBER_CHECK_DUP', function(res) {

      if (res.data.duplicate) {
        alert("동일 이름/전화번호 교사가 이미 존재합니다.");
        return;
      }

      // 정상 → 등록 API 호출
      apiMemberCreate(paramStr);

    }, 'json');
  }

  // 필수항목 체크
  function validateTeacherForm() {

    if ($("input[name='mb_name']").val().trim() === "") {
      alert("이름은 필수 입력 항목입니다.");
      $("input[name='mb_name']").focus();
      return false;
    }

    if ($("input[name='mb_hp']").val().trim() === "") {
      alert("전화번호는 필수 입력 항목입니다.");
      $("input[name='mb_hp']").focus();
      return false;
    }

    if ($("input[name='auth_no']").val().trim() === "") {
      alert("인증번호는 필수 입력 항목입니다.");
      $("input[name='auth_no']").focus();
      return false;
    }

    if ($("input[name='join_date']").val().trim() === "") {
      alert("입사일은 필수 입력 항목입니다.");
      $("input[name='join_date']").focus();
      return false;
    }

    return true;
  }
</script>

<?
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>
