<?php
$sub_menu = '010100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

$g5['title'] = '회원등록';
include_once(G5_NAM_ADM_APTH . '/admin.head.php');

// 파람
$w  = $_REQUEST['w'];
$no = $_REQUEST['no'];

// 파람 초기화
if ( isset($no) ) {
    $row = select_member_one($no);
}
if ( !isset($w) ) {
    $w = "w";
}
?>

<form name="m_form" method="post" action="./member_reg_update.php" autocomplete="off">
    <input type="hidden" name="w" value="<?php echo $w; ?>">
    <input type="hidden" name="no" value="<?php echo $no; ?>">

    <div class="tbl_frm01 tbl_wrap local_sch04">
        <table>
            <caption><?php echo $g5['title']; ?></caption>
            <colgroup>
                <col width="15%">
                <col width="35%">
                <col width="15%">
                <col width="35%">
            </colgroup>
            <tbody>

            <tr>
                <th scope="row">이름</th>
                <td><input type="text" class="frm_input" name="mb_name" value="<?php echo $row['mb_name']; ?>"></td>

                <th scope="row">전화번호</th>
                <td><input type="text" class="frm_input" name="mb_hp" value="<?php echo $row['mb_hp']; ?>"></td>
            </tr>

            <tr>
                <th scope="row">이메일</th>
                <td><input type="text" class="frm_input" name="mb_email" value="<?php echo $row['mb_email']; ?>"></td>

                <th scope="row">주소</th>
                <td><input type="text" class="frm_input" name="mb_addr" value="<?php echo $row['mb_addr']; ?>"></td>
            </tr>

            <tr>
                <th scope="row">성별</th>
                <td>
                    <select name="gender" class="frm_input">
                        <option value="">선택</option>
                        <option value="M" <?php echo $row['gender']=='M'?'selected':''; ?>>남</option>
                        <option value="F" <?php echo $row['gender']=='F'?'selected':''; ?>>여</option>
                    </select>
                </td>

                <th scope="row">반</th>
                <td><input type="text" class="frm_input" name="ban" value="<?php echo $row['ban']; ?>"></td>
            </tr>

            <tr>
                <th scope="row">인증번호</th>
                <td colspan="3">
                    <div style="display:flex; gap:10px; align-items:center;">
                        <input type="text" class="frm_input" name="auth_no" placeholder="숫자 8자리를 입력하세요." value="<?php echo $row['auth_no']; ?>" style="width:200px;">
                        <button type="button" class="btn btn_01">문자발송</button>
                    </div>
                </td>
            </tr>

            <tr>
                <th scope="row">가입일</th>
                <td><input type="date" class="frm_input" name="join_date" value="<?php echo $row['join_date']; ?>"></td>

                <th scope="row">퇴실일</th>
                <td><input type="date" class="frm_input" name="out_date" value="<?php echo $row['out_date']; ?>"></td>
            </tr>

            <tr>
                <th scope="row">상품</th>
                <td>
                    <select name="product" class="frm_input">
                        <option value="">선택</option>
                        <option value="1인실" <?php echo $row['product']=='1인실'?'selected':''; ?>>1인실</option>
                        <option value="2인실" <?php echo $row['product']=='2인실'?'selected':''; ?>>2인실</option>
                    </select>
                </td>

                <th scope="row">금액</th>
                <td><input type="text" class="frm_input" name="price" value="<?php echo $row['price']; ?>"></td>
            </tr>

            <tr>
                <th scope="row">첫달금액</th>
                <td><input type="text" class="frm_input" name="first_price" value="<?php echo $row['first_price']; ?>"></td>

                <th scope="row">마지막달금액</th>
                <td><input type="text" class="frm_input" name="last_price" value="<?php echo $row['last_price']; ?>"></td>
            </tr>

            </tbody>
        </table>
    </div>

    <div class="btn_fixed_top">
        <a href="./member_list.php" class="btn btn_02">목록</a>
        <input type="submit" value="저장" class="btn_submit btn">
    </div>
</form>

<?php
include_once(G5_NAM_ADM_APTH . '/admin.tail.php');
?>
