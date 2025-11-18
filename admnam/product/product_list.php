<?php
include_once('./_common.php');
$sub_menu = '050200'; // 필요시 코드 조정
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '상품 관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

$start = 0;
$num   = defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20;

// 활성 상품만 조회
$list        = select_product_list($start, $num, ['active' => 1]);
$total_count = count(select_product_list(0, 999999, ['active' => 1]));
?>

<script src="<?= G5_API_URL ?>/api_product.js"></script>

<div class="local_ov01 local_ov">
    <span class="ov_txt">총 <?php echo number_format($total_count); ?>건</span>
</div>

<div class="btn_add01 btn_add">
    <button type="button" id="btn-product-add-row" class="btn btn_01">상품 등록</button>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption>상품 관리</caption>
        <thead>
            <tr>
                <th scope="col" style="width:60px;">No</th>
                <th scope="col">상품이름</th>
                <th scope="col" style="width:150px;">상품가격</th>
                <th scope="col" style="width:160px;">관리</th>
            </tr>
        </thead>
        <tbody id="product-tbody">
        <?php if (!empty($list)) { ?>
            <?php
            $no = $total_count - $start;
            foreach ($list as $row) {
                $id          = (int)$row['id'];
                $name        = $row['name'];
                $price       = number_format($row['base_amount']);
                $is_active   = (int)$row['is_active'];
            ?>
            <tr data-id="<?php echo $id; ?>">
                <td class="td_num"><?php echo $no--; ?></td>

                <td class="td_left">
                    <span class="product-name-text"><?php echo htmlspecialchars($name); ?></span>
                    <input type="text" class="frm_input product-name-input" value="<?php echo htmlspecialchars($name); ?>" style="display:none;width:100%;">
                </td>

                <td class="td_left">
                    <span class="product-price-text"><?php echo $price; ?></span>
                    <input type="text" class="frm_input product-price-input" value="<?php echo (int)$row['base_amount']; ?>" style="display:none;width:100px;">
                </td>

                <td>
                    <button type="button" class="btn btn_02 btn-product-edit">수정</button>
                    <button type="button" class="btn btn_02 btn-product-edit-save" style="display:none;">저장</button>
                    <button type="button" class="btn btn_02 btn-product-edit-cancel" style="display:none;">취소</button>
                    <button type="button" class="btn btn_02 btn-product-del">삭제</button>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr>
                <td colspan="4" class="empty_table">등록된 상품이 없습니다.</td>
            </tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
jQuery(function($) {

    function existsAddRow() {
        return $('#product-tbody').find('tr.product-add-row').length > 0;
    }

    // 상품 등록 입력줄 추가
    $('#btn-product-add-row').on('click', function () {
        if (existsAddRow()) {
            $('#product-tbody').find('tr.product-add-row').find('.product-add-name').focus();
            return;
        }

        var $tbody = $('#product-tbody');
        var html = '';

        html += '<tr class="product-add-row">';
        html += '  <td class="td_num">신규</td>';
        html += '  <td class="td_left">';
        html += '      <input type="text" class="frm_input product-add-name" style="width:100%;" placeholder="상품명을 입력하세요.">';
        html += '  </td>';
        html += '  <td class="td_left">';
        html += '      <input type="text" class="frm_input product-add-price" style="width:120px;" placeholder="가격">';
        html += '  </td>';
        html += '  <td>';
        html += '      <button type="button" class="btn btn_01 btn-product-add-save">등록</button>';
        html += '      <button type="button" class="btn btn_02 btn-product-add-cancel">취소</button>';
        html += '  </td>';
        html += '</tr>';

        $tbody.prepend(html);
        $tbody.find('.product-add-name').focus();
    });

    // 상품 등록 저장
    $(document).on('click', '.btn-product-add-save', function () {
        var $tr    = $(this).closest('tr');
        var name   = $.trim($tr.find('.product-add-name').val());
        var price  = $.trim($tr.find('.product-add-price').val());

        if (!name) {
            alert('상품명을 입력하세요.');
            $tr.find('.product-add-name').focus();
            return;
        }

        if (!price || isNaN(price)) {
            alert('가격을 숫자로 입력해주세요.');
            $tr.find('.product-add-price').focus();
            return;
        }

        if (!confirm('새 상품을 등록하시겠습니까?')) return;

        ProductAPI.add({
            name: name,
            base_amount: parseInt(price, 10),
            type_code: 'ROOM',     // 기본값 ROOM
            period_type: 'MONTH',  // 기본값 MONTH
            is_active: 1,
            sort_order: 0
        })
        .done(function(){
            alert('등록되었습니다.');
            location.reload();
        })
        .fail(function(){
            alert('상품 등록에 실패했습니다.');
        });
    });

    // 등록 취소
    $(document).on('click', '.btn-product-add-cancel', function () {
        $(this).closest('tr.product-add-row').remove();
    });

    // 수정 모드 전환
    function setEditMode($tr, isEdit) {
        if (isEdit) {
            $tr.find('.product-name-text').hide();
            $tr.find('.product-price-text').hide();

            $tr.find('.product-name-input').show().focus();
            $tr.find('.product-price-input').show();

            $tr.find('.btn-product-edit').hide();
            $tr.find('.btn-product-del').hide();
            $tr.find('.btn-product-edit-save').show();
            $tr.find('.btn-product-edit-cancel').show();
        } else {
            $tr.find('.product-name-input').hide();
            $tr.find('.product-price-input').hide();

            $tr.find('.product-name-text').show();
            $tr.find('.product-price-text').show();

            $tr.find('.btn-product-edit').show();
            $tr.find('.btn-product-del').show();
            $tr.find('.btn-product-edit-save').hide();
            $tr.find('.btn-product-edit-cancel').hide();
        }
    }

    // 수정 버튼
    $(document).on('click', '.btn-product-edit', function () {
        var $tr = $(this).closest('tr');
        setEditMode($tr, true);
    });

    // 수정 취소
    $(document).on('click', '.btn-product-edit-cancel', function () {
        var $tr = $(this).closest('tr');

        // 원래 값 복구
        var name  = $tr.find('.product-name-text').text();
        var price = $tr.find('.product-price-text').text().replace(/,/g,'');

        $tr.find('.product-name-input').val(name);
        $tr.find('.product-price-input').val(price);

        setEditMode($tr, false);
    });

    // 수정 저장
    $(document).on('click', '.btn-product-edit-save', function () {
        var $tr    = $(this).closest('tr');
        var id     = $tr.data('id');
        var name   = $.trim($tr.find('.product-name-input').val());
        var price  = $.trim($tr.find('.product-price-input').val());

        if (!name) {
            alert('상품명을 입력하세요.');
            return;
        }

        if (!price || isNaN(price)) {
            alert('가격을 숫자로 입력해주세요.');
            return;
        }

        if (!confirm('상품 정보를 수정하시겠습니까?')) return;

        ProductAPI.update(id, {
            name: name,
            base_amount: parseInt(price,10)
        })
        .done(function(){
            alert('수정되었습니다.');
            // 화면 반영
            $tr.find('.product-name-text').text(name);
            $tr.find('.product-price-text').text(Number(price).toLocaleString());
            setEditMode($tr, false);
        })
        .fail(function(){
            alert('상품 수정에 실패했습니다.');
        });
    });

    // 삭제 (soft delete)
    $(document).on('click', '.btn-product-del', function () {
        var $tr = $(this).closest('tr');
        var id  = $tr.data('id');
        var name = $tr.find('.product-name-text').text();

        var msg = '['+name+'] 상품을 삭제(비활성) 처리합니다.\n정말 삭제하시겠습니까?';
        if (!confirm(msg)) return;

        ProductAPI.remove(id)
        .done(function(){
            alert('삭제되었습니다.');
            location.reload();
        })
        .fail(function(){
            alert('삭제 실패');
        });
    });

});
</script>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
?>
