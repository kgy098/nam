<?php
include_once('./_common.php');

$sub_menu = '040800';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '모의고사 과목관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

$start = 0;
$num   = defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20;

// CRUD 호출
$list        = select_mock_subject_list($start, $num);
$total_count = select_mock_subject_listcnt();
?>

<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>

<div class="local_ov01 local_ov">
    <span class="ov_txt">총 <?= number_format($total_count) ?>건</span>
</div>

<div class="btn_add01 btn_add">
    <button type="button" id="btn-subject-add-row" class="btn btn_01">과목 등록</button>
</div>

<div class="tbl_head01 tbl_wrap">
    <table>
        <caption>모의고사 과목관리</caption>
        <thead>
            <tr>
                <th scope="col" style="width:60px;">No</th>
                <th scope="col">과목명</th>
                <th scope="col" style="width:160px;">관리</th>
            </tr>
        </thead>
        <tbody id="subject-tbody">
        <?php if (!empty($list)) { ?>
            <?php
            $no = $total_count - $start;
            foreach ($list as $row) {
                $id   = (int)$row['id'];
                $name = $row['subject_name'];
            ?>
            <tr data-id="<?= $id ?>">
                <td class="td_num"><?= $no-- ?></td>

                <td class="td_left">
                    <span class="subject-name-text"><?= htmlspecialchars($name) ?></span>
                    <input type="text" class="frm_input subject-name-input"
                           value="<?= htmlspecialchars($name) ?>"
                           style="display:none;width:100%;">
                </td>

                <td>
                    <!-- 기본 모드 -->
                    <button type="button" class="btn btn_02 btn-subject-edit">수정</button>
                    <button type="button" class="btn btn_02 btn-subject-del">삭제</button>

                    <!-- 수정 모드 -->
                    <button type="button" class="btn btn_01 btn-subject-edit-save" style="display:none;">저장</button>
                    <button type="button" class="btn btn_02 btn-subject-edit-cancel" style="display:none;">취소</button>
                </td>
            </tr>
            <?php } ?>
        <?php } else { ?>
            <tr><td colspan="3" class="empty_table">등록된 과목이 없습니다.</td></tr>
        <?php } ?>
        </tbody>
    </table>
</div>

<script>
jQuery(function($){

    /* -------------------------------------------------------
     * 신규 입력줄 존재 여부 체크
     * ------------------------------------------------------- */
    function existsAddRow() {
        return $('#subject-tbody').find('tr.subject-add-row').length > 0;
    }

    /* -------------------------------------------------------
     * 신규 등록 입력줄 추가
     * ------------------------------------------------------- */
    $('#btn-subject-add-row').on('click', function () {

        if (existsAddRow()) {
            $('#subject-tbody .subject-add-name').focus();
            return;
        }

        var html = `
        <tr class="subject-add-row">
            <td class="td_num">신규</td>
            <td class="td_left">
                <input type="text" class="frm_input subject-add-name" style="width:100%;" placeholder="과목명을 입력하세요.">
            </td>
            <td>
                <button type="button" class="btn btn_01 btn-subject-add-save">등록</button>
                <button type="button" class="btn btn_02 btn-subject-add-cancel">취소</button>
            </td>
        </tr>
        `;

        $('#subject-tbody').prepend(html);
        $('#subject-tbody .subject-add-name').focus();
    });

    /* 신규 등록 취소 */
    $(document).on('click', '.btn-subject-add-cancel', function () {
        $(this).closest('tr.subject-add-row').remove();
    });

    /* 신규 등록 저장 */
    $(document).on('click', '.btn-subject-add-save', function () {
        var $tr  = $(this).closest('tr');
        var name = $.trim($tr.find('.subject-add-name').val());

        if (!name) {
            alert('과목명을 입력해주세요.');
            return;
        }

        if (!confirm("새 과목을 등록하시겠습니까?")) return;

        apiMockSubject.add({
            subject_name: name
        })
        .done(function(){
            alert('등록되었습니다.');
            location.reload();
        })
        .fail(function(){
            alert('등록 실패');
        });
    });

    /* -------------------------------------------------------
     * 수정 모드 전환
     * ------------------------------------------------------- */
    function setEditMode($tr, isEdit) {

        if (isEdit) {
            $tr.find('.subject-name-text').hide();
            $tr.find('.subject-name-input').show().focus();

            $tr.find('.btn-subject-edit').hide();
            $tr.find('.btn-subject-del').hide();
            $tr.find('.btn-subject-edit-save').show();
            $tr.find('.btn-subject-edit-cancel').show();

        } else {
            $tr.find('.subject-name-input').hide();
            $tr.find('.subject-name-text').show();

            $tr.find('.btn-subject-edit').show();
            $tr.find('.btn-subject-del').show();
            $tr.find('.btn-subject-edit-save').hide();
            $tr.find('.btn-subject-edit-cancel').hide();
        }
    }

    /* 수정 버튼 */
    $(document).on('click', '.btn-subject-edit', function(){
        var $tr = $(this).closest('tr');
        setEditMode($tr, true);
    });

    /* 수정 취소 */
    $(document).on('click', '.btn-subject-edit-cancel', function(){
        var $tr = $(this).closest('tr');
        var original = $.trim($tr.find('.subject-name-text').text());
        $tr.find('.subject-name-input').val(original);
        setEditMode($tr, false);
    });

    /* 수정 저장 */
    $(document).on('click', '.btn-subject-edit-save', function(){
        var $tr  = $(this).closest('tr');
        var id   = $tr.data('id');
        var name = $.trim($tr.find('.subject-name-input').val());

        if (!name) {
            alert('과목명을 입력해주세요.');
            return;
        }

        if (!confirm("과목명을 변경하시겠습니까?")) return;

        apiMockSubject.update(id, { subject_name: name })
            .done(function(){
                alert('수정되었습니다.');
                $tr.find('.subject-name-text').text(name);
                setEditMode($tr, false);
            })
            .fail(function(){
                alert('수정 실패');
            });
    });

    /* -------------------------------------------------------
     * 삭제 (soft delete)
     * ------------------------------------------------------- */
    $(document).on('click', '.btn-subject-del', function(){
        var $tr = $(this).closest('tr');
        var id  = $tr.data('id');
        var name = $.trim($tr.find('.subject-name-text').text());

        if (!confirm(`[${name}] 과목을 삭제하시겠습니까?`)) return;

        apiMockSubject.remove(id)
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
