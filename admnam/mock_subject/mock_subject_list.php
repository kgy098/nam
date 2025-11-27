<?php
include_once('./_common.php');

$sub_menu = '040800';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '과목관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>

<script>
  // 최초 기본 타입
  var CURRENT_TYPE = "";

  // 리스트 렌더링
  function loadSubjectList() {

    apiMockSubject.list(1, 200, {
        subject_type: CURRENT_TYPE
      })
      .done(function(res) {

        var d = res.data;
        var list = d.list || [];
        var total = d.total || 0;

        $(".ov_txt").text("총 " + total + "건");

        var html = "";

        if (list.length === 0) {
          html = '<tr><td colspan="4" class="empty_table">등록된 과목이 없습니다.</td></tr>';
          $("#subject-tbody").html(html);
          return;
        }

        var no = total;
        list.forEach(function(row) {
          html += `
                <tr data-id="${row.id}">

                    <td class="td_num">${no--}</td>

                    <!-- 과목구분 TYPE 표시 -->
                    <td class="td_left">
                        <span class="subject-type-text">${row.type}</span>

                        <select class="frm_input subject-type-input" style="display:none;width:100%;">
                            <option value="일반과목" ${row.type === "일반과목" ? "selected" : ""}>일반과목</option>
                            <option value="모의고사과목" ${row.type === "모의고사과목" ? "selected" : ""}>모의고사과목</option>
                        </select>
                    </td>

                    <td class="td_left">
                        <span class="subject-name-text">${row.subject_name}</span>
                        <input type="text" class="frm_input subject-name-input"
                            value="${row.subject_name}"
                            style="display:none;width:100%;">
                    </td>

                    <td>
                        <button type="button" class="btn btn_02 btn-subject-edit">수정</button>
                        <button type="button" class="btn btn_02 btn-subject-del">삭제</button>

                        <button type="button" class="btn btn_01 btn-subject-edit-save" style="display:none;">저장</button>
                        <button type="button" class="btn btn_02 btn-subject-edit-cancel" style="display:none;">취소</button>
                    </td>
                </tr>`;
        });

        $("#subject-tbody").html(html);
      })
      .fail(function() {
        alert("목록 로딩 실패");
      });
  }
</script>

<!-- ------------------------------ -->
<!-- 상단 영역: 타입 필터 추가 -->
<!-- ------------------------------ -->
<div class="local_ov01 local_ov" style="display:flex; align-items:center; gap:12px;">

  <select id="subject-type-filter" class="frm_input" style="height:32px; width:160px;">
    <option value="">과목구분선택</option>
    <option value="일반과목">일반과목</option>
    <option value="모의고사과목">모의고사과목</option>
  </select>

  <span class="ov_txt"></span>
</div>


<!-- 등록 버튼 -->
<div class="btn_add01 btn_add">
  <button type="button" id="btn-subject-add-row" class="btn btn_01">과목 등록</button>
</div>


<!-- ------------------------------ -->
<!-- 리스트 테이블 (★ type 컬럼 추가됨) -->
<!-- ------------------------------ -->
<div class="tbl_head01 tbl_wrap">
  <table>
    <caption>모의고사 과목관리</caption>
    <thead>
      <tr>
        <th scope="col" style="width:60px;">No</th>
        <th scope="col" style="width:120px;">구분</th> <!-- ★ 신규 -->
        <th scope="col">과목명</th>
        <th scope="col" style="width:160px;">관리</th>
      </tr>
    </thead>
    <tbody id="subject-tbody">
      <tr>
        <td colspan="4" class="empty_table">데이터 불러오는 중..</td>
      </tr>
    </tbody>
  </table>
</div>


<script>
  jQuery(function($) {

    /* 최초 로딩 */
    loadSubjectList();

    /* 타입 변경 시 자동 리스트 갱신 */
    $("#subject-type-filter").on("change", function() {
      CURRENT_TYPE = this.value;
      loadSubjectList();
    });


    /* 신규 입력줄 존재 여부 */
    function existsAddRow() {
      return $('#subject-tbody').find('tr.subject-add-row').length > 0;
    }

    /* 신규 등록 줄 추가 */
    $('#btn-subject-add-row').on('click', function() {

      if (existsAddRow()) {
        $('#subject-tbody .subject-add-name').focus();
        return;
      }

      var html = `
        <tr class="subject-add-row">
            <td class="td_num">신규</td>

            <!-- 과목구분 선택 -->
            <td class="td_left">
                <select class="frm_input subject-add-type" style="width:100%;">
                    <option value="">과목구분선택</option>
                    <option value="일반과목">일반과목</option>
                    <option value="모의고사과목">모의고사과목</option>
                </select>
            </td>

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

    $(document).on('click', '.btn-subject-add-cancel', function() {
      $(this).closest('tr.subject-add-row').remove();
    });


    /* 신규 등록 저장 */
    $(document).on('click', '.btn-subject-add-save', function() {

      var $tr = $(this).closest('tr');
      var name = $.trim($tr.find('.subject-add-name').val());
      var type = $.trim($tr.find('.subject-add-type').val());

      if (!type) {
        alert('과목구분을 선택해주세요.');
        return;
      }
      if (!name) {
        alert('과목명을 입력해주세요.');
        return;
      }

      if (!confirm("새 과목을 등록하시겠습니까?")) return;

      apiMockSubject.add({
          subject_name: name,
          subject_type: type
        })
        .done(function() {
          alert('등록되었습니다.');
          loadSubjectList();
        })
        .fail(function() {
          alert('등록 실패');
        });
    });


    /* 수정 모드 */
    function setEditMode($tr, isEdit) {

      if (isEdit) {
        // 과목명
        $tr.find('.subject-name-text').hide();
        $tr.find('.subject-name-input').show();

        // 과목구분
        $tr.find('.subject-type-text').hide();
        $tr.find('.subject-type-input').show();

        // 버튼
        $tr.find('.btn-subject-edit').hide();
        $tr.find('.btn-subject-del').hide();
        $tr.find('.btn-subject-edit-save').show();
        $tr.find('.btn-subject-edit-cancel').show();

      } else {

        // 과목명
        $tr.find('.subject-name-input').hide();
        $tr.find('.subject-name-text').show();

        // 과목구분
        $tr.find('.subject-type-input').hide();
        $tr.find('.subject-type-text').show();

        // 버튼
        $tr.find('.btn-subject-edit').show();
        $tr.find('.btn-subject-del').show();
        $tr.find('.btn-subject-edit-save').hide();
        $tr.find('.btn-subject-edit-cancel').hide();
      }
    }

    $(document).on('click', '.btn-subject-edit', function() {
      setEditMode($(this).closest('tr'), true);
    });

    $(document).on('click', '.btn-subject-edit-cancel', function() {
      var $tr = $(this).closest('tr');
      var original = $.trim($tr.find('.subject-name-text').text());
      $tr.find('.subject-name-input').val(original);
      setEditMode($tr, false);
    });


    /* 수정 저장 */
    $(document).on('click', '.btn-subject-edit-save', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.subject-name-input').val());

      if (!name) {
        alert('과목명을 입력해주세요.');
        return;
      }

      var msg =
        "과목 정보를 수정하시겠습니까?\n\n" +
        "⚠️ 주의: 과목명 또는 과목구분을 변경하면 다음 데이터에 영향을 줄 수 있습니다.\n" +
        " - 해당 과목이 포함된 모의고사 설정 정보\n" +
        " - 학생들의 학습보고서 및 분석 데이터\n" +
        " - 수업영상, 학습자료 등에서 이 과목을 참조하는 항목\n\n" +
        "변경 내용은 즉시 반영되며 되돌릴 수 없습니다.\n" +
        "정말 수정하시겠습니까?";

      if (!confirm(msg)) return;

      var type = $.trim($tr.find('.subject-type-input').val());

      apiMockSubject.update(id, {
          subject_name: name,
          subject_type: type
        })
        .done(function() {
          alert('수정되었습니다.');
          $tr.find('.subject-name-text').text(name);
          $tr.find('.subject-type-text').text(type);
          setEditMode($tr, false);
        })
        .fail(function() {
          alert('수정 실패');
        });
    });


    /* 삭제 */
    $(document).on('click', '.btn-subject-del', function() {
      var $tr = $(this).closest('tr');
      var id = $tr.data('id');
      var name = $.trim($tr.find('.subject-name-text').text());

      if (!confirm(
        `[${name}] 과목을 삭제하시겠습니까?\n\n` +
        "⚠️ 주의: 과목을 삭제하면 다음 데이터에 영향을 줄 수 있습니다.\n" +
        " - 해당 과목이 포함된 모의고사 설정\n" +
        " - 학생의 학습보고서 \n" +
        " - 강의/수업자료/영상에서 이 과목을 참조하는 항목\n\n" +
        "삭제 후에는 되돌릴 수 없습니다.\n" +
        "정말 삭제하시겠습니까?") ) return;

      apiMockSubject.remove(id)
        .done(function() {
          alert('삭제되었습니다.');
          loadSubjectList();
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