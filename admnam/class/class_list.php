<?php
include_once('./_common.php');
$sub_menu = '040700'; // 필요시 메뉴 코드 변경
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '반 관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

$start = 0;
$num   = defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20;

// ★ active=1 인 반만 조회
$list        = select_class_active(1, $start, $num);
$total_count = count(select_class_active(1, 0, 999999));
?>

<script src="<?= G5_API_URL ?>/api_class.js"></script>

<div class="local_ov01 local_ov">
  <span class="ov_txt">총 <?php echo number_format($total_count); ?>건</span>
</div>

<div class="btn_add01 btn_add">
  <button type="button" id="btn-class-add-row" class="btn btn_01">반 등록</button>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
    <caption>반 관리</caption>
    <thead>
      <tr>
        <th scope="col" style="width:60px;">No</th>
        <th scope="col">반 이름</th>
        <th scope="col" style="width:120px;">상태</th>
        <th scope="col" style="width:160px;">관리</th>
      </tr>
    </thead>
    <tbody id="class-tbody">
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
            <td class="td_left">
              <span class="class-name-text"><?php echo htmlspecialchars($name); ?></span>
              <input type="text" class="frm_input class-name-input" value="<?php echo htmlspecialchars($name); ?>" style="display:none;width:100%;">
            </td>
            <td>
              <span class="class-status-text">
                <?php echo $is_active ? '사용' : '미사용'; ?>
              </span>
            </td>
            <td>
              <button type="button" class="btn btn_02 btn-class-edit">수정</button>
              <button type="button" class="btn btn_02 btn-class-edit-save" style="display:none;">저장</button>
              <button type="button" class="btn btn_02 btn-class-edit-cancel" style="display:none;">취소</button>
              <button type="button" class="btn btn_02 btn-class-del">삭제</button>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td colspan="4" class="empty_table">등록된 반이 없습니다.</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script src="./api_class.js"></script>
<script>
  jQuery(function($) {

    function existsAddRow() {
      return $('#class-tbody').find('tr.class-add-row').length > 0;
    }

    // 반 등록 입력줄 추가
    $('#btn-class-add-row').on('click', function() {
      if (existsAddRow()) {
        $('#class-tbody').find('tr.class-add-row').find('.class-add-name').focus();
        return;
      }

      var $tbody = $('#class-tbody');

      var html = '';
      html += '<tr class="class-add-row">';
      html += '  <td class="td_num">신규</td>';
      html += '  <td class="td_left">';
      html += '      <input type="text" class="frm_input class-add-name" style="width:100%;" placeholder="반 이름을 입력하세요.">';
      html += '  </td>';
      html += '  <td>';
      html += '      <label><input type="checkbox" class="class-add-active" checked> 사용</label>';
      html += '  </td>';
      html += '  <td>';
      html += '      <button type="button" class="btn btn_01 btn-class-add-save">등록</button>';
      html += '      <button type="button" class="btn btn_02 btn-class-add-cancel">취소</button>';
      html += '  </td>';
      html += '</tr>';

      $tbody.prepend(html);
      $tbody.find('tr.class-add-row .class-add-name').focus();
    });

    // 반 등록 - 저장
    $(document).on('click', '.btn-class-add-save', function() {
      var $tr = $(this).closest('tr');
      var name = $.trim($tr.find('.class-add-name').val());
      var useYn = $tr.find('.class-add-active').is(':checked') ? 1 : 0;

      if (!name) {
        alert('반 이름을 입력해주세요.');
        $tr.find('.class-add-name').focus();
        return;
      }

      if (!confirm('새 반을 등록하시겠습니까?')) {
        return;
      }

      ClassAPI.add(name, null, useYn)
        .done(function() {
          alert('등록되었습니다.');
          location.reload();
        })
        .fail(function() {
          alert('반 등록에 실패했습니다.');
        });
    });

    // 반 등록 - 취소
    $(document).on('click', '.btn-class-add-cancel', function() {
      $(this).closest('tr.class-add-row').remove();
    });

    // 수정 모드 전환
    function setEditMode($tr, isEdit) {
      if (isEdit) {
        var name = $.trim($tr.find('.class-name-text').text());
        $tr.find('.class-name-input').val(name);

        $tr.find('.class-name-text').hide();
        $tr.find('.class-name-input').show().focus();

        $tr.find('.btn-class-edit').hide();
        $tr.find('.btn-class-del').hide();
        $tr.find('.btn-class-edit-save').show();
        $tr.find('.btn-class-edit-cancel').show();
      } else {
        $tr.find('.class-name-input').hide();
        $tr.find('.class-name-text').show();

        $tr.find('.btn-class-edit').show();
        $tr.find('.btn-class-del').show();
        $tr.find('.btn-class-edit-save').hide();
        $tr.find('.btn-class-edit-cancel').hide();
      }
    }

    // 수정 버튼
    $(document).on('click', '.btn-class-edit', function() {
      var $tr = $(this).closest('tr');
      setEditMode($tr, true);
    });

    // 수정 취소
    $(document).on('click', '.btn-class-edit-cancel', function() {
      var $tr = $(this).closest('tr');
      var originalName = $.trim($tr.find('.class-name-text').text());
      $tr.find('.class-name-input').val(originalName);
      setEditMode($tr, false);
    });

    // 수정 저장
    $(document).on('click', '.btn-class-edit-save', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.class-name-input').val());

      if (!name) {
        alert('반 이름을 입력해주세요.');
        $tr.find('.class-name-input').focus();
        return;
      }

      if (!confirm('반 이름을 변경하면 이 반에 배정된 학생들의 반 표기에도 모두 반영됩니다.\n정말 변경하시겠습니까?')) {
        return;
      }

      ClassAPI.update(id, {
          name: name
        })
        .done(function() {
          alert('수정되었습니다.');
          // 화면 반영
          $tr.find('.class-name-text').text(name);
          setEditMode($tr, false);
        })
        .fail(function() {
          alert('반 수정에 실패했습니다.');
        });
    });

    // 삭제(soft delete: is_active = 0)
    $(document).on('click', '.btn-class-del', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.class-name-text').text());

      var msg = '';
      msg += '[' + name + '] 반을 삭제(비활성) 처리합니다.\n';
      msg += '이 반에 배정된 학생이 있을 경우, 해당 학생들의 반 정보 표시나 조회에 영향이 있을 수 있습니다.\n';
      msg += '정말 삭제(비활성)하시겠습니까?';

      if (!confirm(msg)) {
        return;
      }

      ClassAPI.setActive(id, 0)
        .done(function() {
          alert('삭제(비활성)되었습니다.');
          location.reload();
        })
        .fail(function() {
          alert('반 삭제(비활성)에 실패했습니다.');
        });
    });

  });
</script>

<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
