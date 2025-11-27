<?php
include_once('./_common.php');

$sub_menu = '040310';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '모의고사 관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

$start = 0;
$num   = defined('CN_PAGE_NUM') ? CN_PAGE_NUM : 20;

// CRUD 호출
$list        = select_mock_test_list($list_params);
$total_count = select_mock_test_listcnt($list_params);
?>

<script src="<?= G5_API_URL ?>/api_mock_test.js"></script>

<div class="local_ov01 local_ov">
  <span class="ov_txt">총 <?= number_format($total_count) ?>건</span>
</div>

<div class="btn_add01 btn_add">
  <button type="button" id="btn-test-add-row" class="btn btn_01">모의고사 등록</button>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
    <caption>모의고사 관리</caption>
    <thead>
      <tr>
        <th scope="col" style="width:60px;">No</th>
        <th scope="col">모의고사명</th>
        <th scope="col" style="width:120px;">접수시작</th>
        <th scope="col" style="width:120px;">접수종료</th>
        <th scope="col" style="width:120px;">시험일</th>
        <th scope="col" style="width:100px;">상태</th>
        <th scope="col" style="width:160px;">관리</th>
      </tr>
    </thead>
    <tbody id="test-tbody">
      <?php if (!empty($list)) { ?>
        <?php
        $no = $total_count - $start;
        foreach ($list as $row) {
          $id    = (int)$row['id'];
          $name  = $row['name'];
          $as    = $row['apply_start'];
          $ae    = $row['apply_end'];
          $ed    = $row['exam_date'];
          $st    = $row['status'];
        ?>
          <tr data-id="<?= $id ?>">
            <td class="td_num"><?= $no-- ?></td>

            <!-- 모의고사명 -->
            <td class="td_left">
              <span class="test-name-text"><?= htmlspecialchars($name) ?></span>
              <input type="text" class="frm_input test-name-input" value="<?= htmlspecialchars($name) ?>" style="display:none;width:100%;">
            </td>

            <!-- 접수 시작 -->
            <td>
              <span class="test-as-text"><?= $as ?></span>
              <input type="date" class="frm_input test-as-input" value="<?= $as ?>" style="display:none;width:100%;">
            </td>

            <!-- 접수 종료 -->
            <td>
              <span class="test-ae-text"><?= $ae ?></span>
              <input type="date" class="frm_input test-ae-input" value="<?= $ae ?>" style="display:none;width:100%;">
            </td>

            <!-- 시험일 -->
            <td>
              <span class="test-ed-text"><?= $ed ?></span>
              <input type="date" class="frm_input test-ed-input" value="<?= $ed ?>" style="display:none;width:100%;">
            </td>

            <!-- 상태 -->
            <td>
              <span class="test-status-text"><?= $st ?></span>
              <select class="frm_input test-status-input" style="display:none;width:100%;">
                <option value="접수전" <?= $st == '접수전' ? 'selected' : '' ?>>접수전</option>
                <option value="접수중" <?= $st == '접수중' ? 'selected' : '' ?>>접수중</option>
                <option value="접수마감" <?= $st == '접수마감' ? 'selected' : '' ?>>접수마감</option>
              </select>
            </td>

            <!-- 관리 -->
            <td>
              <!-- 기본 모드 -->
              <button type="button" class="btn btn_02 btn-test-edit">수정</button>
              <button type="button" class="btn btn_02 btn-test-del">삭제</button>

              <!-- 수정 모드 -->
              <button type="button" class="btn btn_01 btn-test-edit-save" style="display:none;">저장</button>
              <button type="button" class="btn btn_02 btn-test-edit-cancel" style="display:none;">취소</button>
            </td>
          </tr>
        <?php } ?>
      <?php } else { ?>
        <tr>
          <td colspan="7" class="empty_table">등록된 모의고사가 없습니다.</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<script>
  jQuery(function($) {

    /* -------------------------------------------------------
     * 신규 입력줄 존재 여부 체크
     * ------------------------------------------------------- */
    function existsAddRow() {
      return $('#test-tbody').find('tr.test-add-row').length > 0;
    }

    /* -------------------------------------------------------
     * 신규 등록 입력줄 추가
     * ------------------------------------------------------- */
    $('#btn-test-add-row').on('click', function() {

      if (existsAddRow()) {
        $('#test-tbody .test-add-name').focus();
        return;
      }

      var html = `
        <tr class="test-add-row">
            <td class="td_num">신규</td>
            <td><input type="text" class="frm_input test-add-name" style="width:100%;" placeholder="모의고사명을 입력하세요."></td>
            <td><input type="date" class="frm_input test-add-as" style="width:100%;"></td>
            <td><input type="date" class="frm_input test-add-ae" style="width:100%;"></td>
            <td><input type="date" class="frm_input test-add-ed" style="width:100%;"></td>
            <td>
                <select class="frm_input test-add-status" style="width:100%;">
                    <option value="접수중">접수중</option>
                    <option value="마감">마감</option>
                    <option value="완료">완료</option>
                </select>
            </td>
            <td>
                <button type="button" class="btn btn_01 btn-test-add-save">등록</button>
                <button type="button" class="btn btn_02 btn-test-add-cancel">취소</button>
            </td>
        </tr>
        `;

      $('#test-tbody').prepend(html);
      $('#test-tbody .test-add-name').focus();
    });

    /* 신규 등록 취소 */
    $(document).on('click', '.btn-test-add-cancel', function() {
      $(this).closest('tr.test-add-row').remove();
    });

    /* 신규 등록 저장 */
    $(document).on('click', '.btn-test-add-save', function() {

      var $tr = $(this).closest('tr');
      var name = $.trim($tr.find('.test-add-name').val());

      if (!name) {
        alert('모의고사명을 입력해주세요.');
        return;
      }

      var payload = {
        name: name,
        apply_start: $tr.find('.test-add-as').val(),
        apply_end: $tr.find('.test-add-ae').val(),
        exam_date: $tr.find('.test-add-ed').val(),
        status: $tr.find('.test-add-status').val()
      };

      if (!confirm("새 모의고사를 등록하시겠습니까?")) return;

      apiMockTest.add(payload)
        .done(function() {
          alert('등록되었습니다.');
          location.reload();
        })
        .fail(function() {
          alert('등록 실패');
        });
    });


    /* -------------------------------------------------------
     * 수정 모드 전환
     * ------------------------------------------------------- */
    function setEditMode($tr, isEdit) {

      var inputs = ['name', 'as', 'ae', 'ed', 'status'];

      if (isEdit) {
        $tr.find('.test-name-text').hide();
        $tr.find('.test-name-input').show().focus();

        $tr.find('.test-as-text').hide();
        $tr.find('.test-as-input').show();

        $tr.find('.test-ae-text').hide();
        $tr.find('.test-ae-input').show();

        $tr.find('.test-ed-text').hide();
        $tr.find('.test-ed-input').show();

        $tr.find('.test-status-text').hide();
        $tr.find('.test-status-input').show();

        $tr.find('.btn-test-edit').hide();
        $tr.find('.btn-test-del').hide();
        $tr.find('.btn-test-edit-save').show();
        $tr.find('.btn-test-edit-cancel').show();
      } else {
        $tr.find('.test-name-input').hide();
        $tr.find('.test-name-text').show();

        $tr.find('.test-as-input').hide();
        $tr.find('.test-as-text').show();

        $tr.find('.test-ae-input').hide();
        $tr.find('.test-ae-text').show();

        $tr.find('.test-ed-input').hide();
        $tr.find('.test-ed-text').show();

        $tr.find('.test-status-input').hide();
        $tr.find('.test-status-text').show();

        $tr.find('.btn-test-edit').show();
        $tr.find('.btn-test-del').show();
        $tr.find('.btn-test-edit-save').hide();
        $tr.find('.btn-test-edit-cancel').hide();
      }
    }

    /* 수정 버튼 */
    $(document).on('click', '.btn-test-edit', function() {
      var $tr = $(this).closest('tr');
      setEditMode($tr, true);
    });

    /* 수정 취소 */
    $(document).on('click', '.btn-test-edit-cancel', function() {
      var $tr = $(this).closest('tr');

      $tr.find('.test-name-input').val($tr.find('.test-name-text').text());
      $tr.find('.test-as-input').val($tr.find('.test-as-text').text());
      $tr.find('.test-ae-input').val($tr.find('.test-ae-text').text());
      $tr.find('.test-ed-input').val($tr.find('.test-ed-text').text());
      $tr.find('.test-status-input').val($tr.find('.test-status-text').text());

      setEditMode($tr, false);
    });

    /* 수정 저장 */
    $(document).on('click', '.btn-test-edit-save', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');

      var payload = {
        name: $.trim($tr.find('.test-name-input').val()),
        apply_start: $tr.find('.test-as-input').val(),
        apply_end: $tr.find('.test-ae-input').val(),
        exam_date: $tr.find('.test-ed-input').val(),
        status: $tr.find('.test-status-input').val()
      };

      if (!payload.name) {
        alert('모의고사명을 입력해주세요.');
        return;
      }

      if (!confirm("수정하시겠습니까?")) return;

      apiMockTest.update(id, payload)
        .done(function() {
          alert('수정되었습니다.');
          $tr.find('.test-name-text').text(payload.name);
          $tr.find('.test-as-text').text(payload.apply_start);
          $tr.find('.test-ae-text').text(payload.apply_end);
          $tr.find('.test-ed-text').text(payload.exam_date);
          $tr.find('.test-status-text').text(payload.status);
          setEditMode($tr, false);
        })
        .fail(function() {
          alert('수정 실패');
        });
    });


    /* -------------------------------------------------------
     * 삭제
     * ------------------------------------------------------- */
    $(document).on('click', '.btn-test-del', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.test-name-text').text());

      if (!confirm(`[${name}] 모의고사를 삭제하시겠습니까?`)) return;

      apiMockTest.delete(id)
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
include_once(G5_ADMIN_PATH . '/admin.tail.php');
?>