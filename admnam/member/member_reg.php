<?
$sub_menu = '010100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = '회원등록';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

// 파람
$w     = $_REQUEST['w']     ?? '';
$mb_id = $_REQUEST['mb_id'] ?? '';

$defaults = get_member_form_defaults();
$db_row   = [];

// 등록모드
if ($w === '' || $w === 'w') {

  $row = $defaults;

  // 수정모드
} else if ($w === 'u' && $mb_id !== '') {

  $db_row = select_member_one_by_id($mb_id);

  if (!$db_row) alert("회원 정보를 찾을 수 없습니다.");

  $row = array_merge($defaults, $db_row);
} else {
  alert("잘못된 요청입니다.");
}
?>

<span>첫달금액을 입력하지 않으면 상품금액이 자동으로 저장됩니다.</span>

<form name="m_form" id="m_form" method="post">
  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="mb_id" value="<?= $mb_id ?>">

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
          <th class="required">성별</th>
          <td>
            <select name="mb_sex" class="frm_input">
              <option value="">선택</option>
              <option value="남" <?= $row['mb_sex'] == '남' ? 'selected' : '' ?>>남</option>
              <option value="여" <?= $row['mb_sex'] == '여' ? 'selected' : '' ?>>여</option>
            </select>
          </td>

          <th class="required">반</th>
          <td>
            <select name="class" id="class" class="frm_input" data-selected="<?= $row['class'] ?>">
              <option value="">선택</option>
            </select>
          </td>
        </tr>

        <tr>
          <th class="required">인증번호</th>
          <td colspan="3">
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="text" name="auth_no" class="frm_input" value="<?= $row['auth_no'] ?>" placeholder="8자리" style="width:200px;">
              <?php if ($w === 'u') { ?>
                <!-- 수정모드: 활성 버튼 -->
                <button type="button" class="btn btn_01">문자발송</button>
              <?php } else { ?>
                <!-- 등록모드: 비활성 버튼 -->
                <button type="button" class="btn btn_01" disabled
                  style="opacity:0.5; cursor:not-allowed;">문자발송</button>
              <?php } ?>
              <span style="color:#777; font-size:12px;">
                문자발송은 회원등록시 자동으로 발송되며, 회원등록후에 문자발송 버튼이 활성화됩니다.
              </span>
            </div>
          </td>
        </tr>

        <tr>
          <th class="required">입실일</th>
          <td><input type="date" name="join_date" class="frm_input" value="<?= $row['join_date'] ?>"></td>

          <th>퇴실일</th>
          <td><input type="date" name="out_date" class="frm_input" value="<?= $row['out_date'] ?>"></td>
        </tr>

        <tr>
          <th class="required">상품</th>
          <td>
            <select name="product_id" id="product" class="frm_input" data-selected="<?= $row['product_id'] ?>">
              <option value="">선택</option>
            </select>
          </td>

          <th class="required">금액</th>
          <td><input type="text" name="product_price" id="product_price" class="frm_input" value="<?= $row['product_price'] ?>"></td>
        </tr>

        <tr>
          <th>첫달금액</th>
          <td><input type="text" name="product_price_first" class="frm_input" value="<?= $row['product_price_first'] ?>"></td>

          <th>마지막달금액</th>
          <td><input type="text" name="product_price_last" class="frm_input" value="<?= $row['product_price_last'] ?>"></td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./member_list.php" class="btn btn_02">목록</a>
    <button type="button" class="btn_submit btn" onclick="saveMember()">저장</button>
  </div>
</form>

<script src="<?= G5_API_URL ?>/api_member.js"></script>
<script src="<?= G5_API_URL ?>/api_product.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<script>
  $(function() {
    loadProductList();
    loadClassList();

    $(document).on("input", "input[name='mb_hp']", function() {
      this.value = this.value.replace(/[^0-9]/g, '');
    });
  });

  /* 상품 */
  function loadProductList() {
    let $product = $('#product');
    let selected = $product.data('selected') || '';

    productAPI.list({}, 1, 100).then(res => {
      if (!res || res.result !== 'SUCCESS') return;

      let html = '<option value="">선택</option>';
      $.each(res.data, (i, row) => {
        let sel = (String(selected) == String(row.id)) ? 'selected' : '';
        html += `<option value="${row.id}" ${sel}>${row.name}</option>`;
      });

      $product.html(html);

      // 자동 가격 세팅
      $product.on('change', function() {
        let id = $(this).val();
        if (!id) {
          $('#product_price').val('');
          return;
        }

        productAPI.get(id).then(r => {
          if (r && r.result === 'SUCCESS') {
            let amount = r.data.base_amount || 0;
            $('#product_price').val(number_format(amount));
          }
        });
      });
    });
  }

  /* 반 */
  function loadClassList() {
    let $class = $('#class');
    let selected = $class.data('selected') || '';

    apiClass.list(1, 100).done(res => {
      if (!res || res.result !== 'SUCCESS') return;

      let html = '<option value="">선택</option>';
      $.each(res.data.list || res.data, (i, row) => {
        let sel = (String(selected) == String(row.id)) ? 'selected' : '';
        html += `<option value="${row.id}" ${sel}>${row.name}</option>`;
      });

      $class.html(html);
    });
  }


  /* 저장 */
  function saveMember() {

    if (!validateMemberForm()) return;

    let data = $("#m_form").serialize();
    let w = $("input[name='w']").val();

    if (w === 'u') {
      // 수정 모드 → UPDATE 호출
      memberAPI.update(data).then(res => {
        if (res.result === 'SUCCESS') {
          alert('수정되었습니다.');
          location.href = './member_list.php';
        } else {
          alert('수정 실패: ' + (res.data || '오류'));
        }
      });

    } else {
      // 등록 모드 → CREATE 호출
      memberAPI.create(data).then(res => {
        if (res.result === 'SUCCESS') {
          alert('등록되었습니다.');
          location.href = './member_list.php';
        } else {
          alert('등록 실패: ' + (res.data || '오류'));
        }
      });
    }
  }

  /* 유효성 검사 */
  function validateMemberForm() {

    if ($("input[name='mb_name']").val().trim() === '') {
      alert("이름을 입력하세요");
      return false;
    }
    if ($("input[name='mb_hp']").val().trim() === '') {
      alert("전화번호를 입력하세요");
      return false;
    }
    if ($("select[name='mb_sex']").val() === '') {
      alert("성별을 선택하세요");
      return false;
    }
    if ($("input[name='auth_no']").val().trim() === '') {
      alert("인증번호를 입력하세요");
      return false;
    }
    if ($("input[name='join_date']").val() === '') {
      alert("입실일을 입력하세요");
      return false;
    }
    if ($("select[name='product_id']").val() === '') {
      alert("상품을 선택하세요");
      return false;
    }
    if ($("input[name='product_price']").val().trim() === '') {
      alert("금액을 입력하세요");
      return false;
    }

    return true;
  }
</script>

<?
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>