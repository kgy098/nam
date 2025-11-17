<?
$sub_menu = '010100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = '회원등록';
include_once(G5_NAM_ADM_APTH . '/admin.head.php');

// 파람
$w  = $_REQUEST['w'] ?? '';
$no = $_REQUEST['no'] ?? '';

$defaults = get_member_form_defaults();

// 파람 초기화
if (isset($no)) {
  $db_row = select_member_one($no);
}
$row = array_merge($defaults, $db_row);

if (!isset($w)) {
  $w = "w";
}
?>

<script src="<?= G5_API_URL ?>/api_member.js"></script>
<script src="<?= G5_API_URL ?>/api_product.js"></script>

<form name="m_form" id="m_form" method="post" autocomplete="off">
  <input type="hidden" name="w" value="<?= $w; ?>">
  <input type="hidden" name="no" value="<?= $no; ?>">

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
          <th scope="row">성별</th>
          <td>
            <select name="gender" class="frm_input">
              <option value="">선택</option>
              <option value="M" <?= $row['gender'] == 'M' ? 'selected' : ''; ?>>남</option>
              <option value="F" <?= $row['gender'] == 'F' ? 'selected' : ''; ?>>여</option>
            </select>
          </td>

          <th scope="row">반</th>
          <td><input type="text" class="frm_input" name="ban" value="<?= $row['ban']; ?>"></td>
        </tr>

        <tr>
          <th scope="row">인증번호</th>
          <td colspan="3">
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="text" class="frm_input" name="auth_no" placeholder="숫자 8자리를 입력하세요." value="<?= $row['auth_no']; ?>" style="width:200px;">
              <button type="button" class="btn btn_01">문자발송</button>
            </div>
          </td>
        </tr>

        <tr>
          <th scope="row">가입일</th>
          <td><input type="date" class="frm_input" name="join_date" value="<?= $row['join_date']; ?>"></td>

          <th scope="row">퇴실일</th>
          <td><input type="date" class="frm_input" name="out_date" value="<?= $row['out_date']; ?>"></td>
        </tr>

        <tr>
          <th scope="row">상품</th>
          <td>
            <select name="product" id="product" class="frm_input" data-selected="<?= $row['product']; ?>">
              <option value="">선택</option>
            </select>
          </td>

          <th scope="row">금액</th>
          <td><input type="text" class="frm_input" name="price" id="price" value="<?= $row['price']; ?>"></td>
        </tr>

        <tr>
          <th scope="row">첫달금액</th>
          <td><input type="text" class="frm_input" name="first_price" value="<?= $row['first_price']; ?>"></td>

          <th scope="row">마지막달금액</th>
          <td><input type="text" class="frm_input" name="last_price" value="<?= $row['last_price']; ?>"></td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./member_list.php" class="btn btn_02">목록</a>
    <input type="submit" value="등록" class="btn_submit btn" onclick="createMember();">
  </div>
</form>

<script>
  $(document).ready(function() {
    loadProductList();
  });

  function loadProductList() {
    var $product = $('#product');
    if (!$product.length) return; // HTML이 아직 없으면 종료

    var selectedValue = $product.data('selected') || '';
    // PHP에서 selected 적용한 경우를 대비해서 가져옴

    ProductAPI.list(1, 100).then(function(res) {
      if (!res || res.result !== 'SUCCESS') return;

      var html = '<option value="">선택하세요</option>';

      $.each(res.data, function(i, row) {
        var sel = (String(selectedValue) === String(row.id)) ? ' selected' : '';
        html += '<option value="' + row.id + '"' + sel + '>' + row.name + '</option>';
      });

      $product.html(html);

      // 이벤트 중복 방지 후 다시 바인딩
      $product.off('change').on('change', function() {
        var productId = $(this).val();
        if (!productId) {
          $('#price').val('');
          return;
        }

        // 단일 상품 조회 API 호출
        ProductAPI.get(productId).then(function(res2) {
          if (res2 && res2.result === 'SUCCESS') {
            var amount = res2.data.base_amount ? res2.data.base_amount : 0;
            $('#price').val(number_format(amount));
          }
        });
      });
    });
  }

  function createMember() {
    // if (!validateMemberForm()) return;

    var paramStr = $("#m_form").serialize();
    // console.log(JSON.stringify(paramStr)); alert("TEST");

    $.post(g5_ctrl_url + '/ctrl_member.php', paramStr + '&type=MEMBER_CHECK_DUP', function(res) {

      if (res.data.duplicate) {
        alert("동일 이름/전화번호 회원이 이미 존재합니다.");
        return; // 🔥 등록 중단
      }

      // 중복 아님 → 정상 등록
      apiMemberCreate(paramStr);

    }, 'json');

  }

  function validateMemberForm() {

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

    if ($("select[name='gender']").val().trim() === "") {
      alert("성별을 선택해 주세요.");
      $("select[name='gender']").focus();
      return false;
    }

    if ($("input[name='auth_no']").val().trim() === "") {
      alert("인증번호는 필수 입력 항목입니다.");
      $("input[name='auth_no']").focus();
      return false;
    }

    if ($("input[name='join_date']").val().trim() === "") {
      alert("가입일(입실일시)은 필수 입력 항목입니다.");
      $("input[name='join_date']").focus();
      return false;
    }

    if ($("select[name='product']").val().trim() === "") {
      alert("상품을 선택해 주세요.");
      $("select[name='product']").focus();
      return false;
    }

    if ($("input[name='price']").val().trim() === "") {
      alert("금액은 필수 입력 항목입니다.");
      $("input[name='price']").focus();
      return false;
    }

    return true; // 모든 검증 통과 → 등록 가능
  }
</script>

<?
include_once(G5_NAM_ADM_APTH . '/admin.tail.php');
?>