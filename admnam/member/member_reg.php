<?
$sub_menu = '010100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = '회원등록';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

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

<span>첫달금액을 입력하지 않으면 상품의 금액이 자동으로 저장됩니다.<br></span>

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
              <option value="남" <?= $row['gender'] == '남' ? 'selected' : ''; ?>>남</option>
              <option value="여" <?= $row['gender'] == '여' ? 'selected' : ''; ?>>여</option>
            </select>
          </td>

          <th scope="row">반</th>
          <td>
            <select name="class" id="class" class="frm_input" data-selected="<?= $row['class']; ?>">
              <option value="">선택</option>
            </select>
          </td>
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
          <th scope="row">입실일</th>
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
    <input type="button" value="등록" class="btn_submit btn" onclick="createMember();">
  </div>
</form>

<script src="<?= G5_API_URL ?>/api_member.js"></script>
<script src="<?= G5_API_URL ?>/api_product.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<script>
$(document).ready(function () {
    loadProductList();
    loadClassList();
});

/* ==========================================================
   1) 상품 목록 로딩
========================================================== */
function loadProductList() {

    var $product = $('#product');
    var selectedValue = $product.data('selected') || '';

    productAPI.list({}, 1, 100).then(function (res) {

        if (!res || res.result !== 'SUCCESS') return;

        var html = '<option value="">선택</option>';

        $.each(res.data, function (i, row) {
            var sel = (String(selectedValue) === String(row.id)) ? ' selected' : '';
            html += `<option value="${row.id}" ${sel}>${row.name}</option>`;
        });

        $product.html(html);

        // 상품 선택 시 금액 자동 세팅
        $product.on('change', function () {
            var productId = $(this).val();
            if (!productId) {
                $('#price').val('');
                return;
            }

            productAPI.get(productId).then(function (res2) {
                if (res2 && res2.result === 'SUCCESS') {
                    var amount = res2.data.base_amount || 0;
                    $('#price').val(number_format(amount));
                }
            });
        });
    });
}

/* ==========================================================
   2) 반 목록 로딩
========================================================== */
function loadClassList() {

    var $class = $('#class');
    var selected = $class.data('selected') || '';

    apiClass.list(1, 100).done(function (res) {

        if (!res || res.result !== 'SUCCESS') return;

        var html = '<option value="">선택</option>';

        $.each(res.data.list || res.data, function (i, row) {
            var sel = (String(selected) === String(row.id)) ? ' selected' : '';
            html += `<option value="${row.id}" ${sel}>${row.name}</option>`;
        });

        $class.html(html);

    });
}

/* ==========================================================
   3) 회원 등록 처리
========================================================== */
function createMember() {

    var paramStr = $("#m_form").serialize();

    // 0) 등록 버튼이 submit이라면 폼이 제출됨 → 방지
    event.preventDefault();

    // 1) 중복 체크
    $.post(g5_ctrl_url + '/ctrl_member.php', paramStr + '&type=MEMBER_CHECK_DUP', function (res) {

        if (res.data.duplicate) {
            alert("동일 이름/전화번호 회원이 이미 존재합니다.");
            return false;
        }

        // 2) first_price 공백이면 → price 값 그대로 자동 세팅
        let price = $("input[name='price']").val();
        let first_price = $("input[name='first_price']").val();
        if (first_price.trim() === "" && price.trim() !== "") {
            $("input[name='first_price']").val(price);
        }

        // 3) 실제 등록
        memberAPI.create(paramStr).then(function (r) {

            if (!r) {
                alert("등록 실패(응답 없음)");
                return false;
            }

            if (r.result === "SUCCESS") {
                alert("저장되었습니다.");
                location.href = './member_list.php';
            } else {
                alert("저장 실패: " + (r.data || "오류"));
            }

        }).catch(function () {
            alert("등록 중 오류가 발생했습니다.");
        });

    }, 'json');

    return false; // 폼 submit 차단
}

/* ==========================================================
   4) 필수값 검증
========================================================== */
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
        alert("입실일은 필수 입력 항목입니다.");
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

    return true;
}
</script>


<?
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>