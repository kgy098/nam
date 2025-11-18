<?php
$sub_menu = '040600';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '출석 구분 관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

$start = 0;
$num   = defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20;

// ★ active=1만 출력 (반 관리 방식 동일)
$list        = select_attendance_type_active(1, $start, $num);
$total_count = count(select_attendance_type_active(1, 0, 999999));
?>

<script src="<?= G5_API_URL ?>/api_attendance_type.js"></script>


<div class="local_ov01 local_ov">
  <span class="ov_txt">총 <?php echo number_format($total_count); ?>건</span>
</div>

<div class="btn_add01 btn_add">
  <button type="button" id="btn-att-add-row" class="btn btn_01">출석 구분 등록</button>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
    <caption>출석 구분 관리</caption>
    <thead>
      <tr>
        <th scope="col" style="width:60px;">No</th>
        <th scope="col">구분명</th>
        <th scope="col" style="width:100px;">상태</th>
        <th scope="col" style="width:160px;">관리</th>
      </tr>
    </thead>
    <tbody id="att-tbody">
      <?php if (!empty($list)) { ?>
        <?php
        $no = $total_count - $start;
        foreach ($list as $row) {
          $id        = (int)$row['id'];
          $name      = $row['name'];
          $is_active = (int)$row['is_active'];
        ?>
          <tr data-id="<?php echo $id; ?>">
            <td class="td_num"><?php echo $no--; ?></td>

            <!-- 구분명 -->
            <td class="td_left">
              <span class="att-name-text"><?php echo htmlspecialchars($name); ?></span>
              <input type="text" class="frm_input att-name-input"
                value="<?php echo htmlspecialchars($name); ?>"
                style="display:none;width:100%;">
            </td>

            <!-- 상태 -->
            <td><?php echo $is_active ? '사용' : '미사용'; ?></td>

            <!-- 관리 -->
            <td>
              <button type="button" class="btn btn_02 btn-att-edit">수정</button>
              <button type="button" class="btn btn_02 btn-att-edit-save" style="display:none;">저장</button>
              <button type="button" class="btn btn_02 btn-att-edit-cancel" style="display:none;">취소</button>
              <button type="button" class="btn btn_02 btn-att-del">삭제</button>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td colspan="4" class="empty_table">등록된 출석 구분이 없습니다.</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
  jQuery(function($) {

    function existsAddRow() {
      return $('#att-tbody').find('tr.att-add-row').length > 0;
    }

    // 등록 입력 줄 추가
    $('#btn-att-add-row').on('click', function() {
      if (existsAddRow()) {
        $('.att-add-name').focus();
        return;
      }

      var html = '';
      html += '<tr class="att-add-row">';
      html += '  <td class="td_num">신규</td>';
      html += '  <td><input type="text" class="frm_input att-add-name" style="width:100%;" placeholder="구분명"></td>';
      html += '  <td><label><input type="checkbox" class="att-add-active" checked> 사용</label></td>';
      html += '  <td>';
      html += '    <button type="button" class="btn btn_01 btn-att-add-save">등록</button>';
      html += '    <button type="button" class="btn btn_02 btn-att-add-cancel">취소</button>';
      html += '  </td>';
      html += '</tr>';

      $('#att-tbody').prepend(html);
      $('.att-add-name').focus();
    });

    // 등록 저장
    $(document).on('click', '.btn-att-add-save', function() {
      var $tr = $(this).closest('tr');
      var name = $.trim($tr.find('.att-add-name').val());
      var useYn = $tr.find('.att-add-active').is(':checked') ? 1 : 0;

      if (!name) {
        alert('구분명을 입력해주세요.');
        return;
      }

      if (!confirm('출석 구분을 등록하시겠습니까?')) return;

      AttendanceTypeAPI.create({
        name: name,
        is_active: useYn
      }).done(function() {
        alert('등록되었습니다.');
        location.reload();
      }).fail(function() {
        alert('등록 실패');
      });
    });

    $(document).on('click', '.btn-att-add-cancel', function() {
      $(this).closest('tr.att-add-row').remove();
    });

    // 수정 모드
    function setEditMode($tr, isEdit) {
      if (isEdit) {
        $tr.find('.att-name-text').hide();
        $tr.find('.att-name-input').show();

        $tr.find('.btn-att-edit').hide();
        $tr.find('.btn-att-del').hide();
        $tr.find('.btn-att-edit-save, .btn-att-edit-cancel').show();
      } else {
        $tr.find('.att-name-input').hide();
        $tr.find('.att-name-text').show();

        $tr.find('.btn-att-edit').show();
        $tr.find('.btn-att-del').show();
        $tr.find('.btn-att-edit-save, .btn-att-edit-cancel').hide();
      }
    }

    // 수정 시작
    $(document).on('click', '.btn-att-edit', function() {
      setEditMode($(this).closest('tr'), true);
    });

    // 수정 취소
    $(document).on('click', '.btn-att-edit-cancel', function() {
      setEditMode($(this).closest('tr'), false);
    });

    // 저장
    $(document).on('click', '.btn-att-edit-save', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.att-name-input').val());

      if (!name) {
        alert('구분명을 입력해주세요.');
        return;
      }

      if (!confirm('수정하시겠습니까?')) return;

      AttendanceTypeAPI.update(id, {
          name: name
        })
        .done(function() {
          alert('수정되었습니다.');
          location.reload();
        })
        .fail(function() {
          alert('수정 실패');
        });
    });

    // 삭제
    $(document).on('click', '.btn-att-del', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.att-name-text').text());

      if (!confirm('[' + name + '] 구분을 삭제합니다.\n정말 삭제하시겠습니까?')) return;

      AttendanceTypeAPI.remove(id)
        .done(function() {
          alert('삭제되었습니다.');
          location.reload();
        })
        .fail(function() {
          alert('삭제 실패');
        });
    });

  });
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
