<?php
$sub_menu = '040200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, "w");

include_once(G5_EDITOR_LIB); // 그누보드 기본 에디터 로드

// g5_board_file용 bo_table 고정
$bo_table = 'cn_study_report';

// 파라미터
$w  = $_GET['w'] ?? '';
$id = (int)($_GET['id'] ?? 0);

$row = [];
$file_row = [];

if ($w === 'u') {
  // 학습보고서 기본 정보
  $row = select_study_report_one($id);
  if (!$row) {
    alert('자료가 존재하지 않습니다.');
  }

  // 첨부파일 조회 (단일 파일)
  $file_row = get_board_file($bo_table, $id, 0);
}

$page_title = ($w === 'u') ? '학습보고서 수정' : '학습보고서 등록';
$g5['title'] = $page_title;

include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<form name="report_form" id="report_form" method="post" enctype="multipart/form-data" autocomplete="off">
  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="id" value="<?= $id ?>">
  <input type="hidden" name="bo_table" value="<?= $bo_table ?>">

  <div class="tbl_frm01 tbl_wrap local_sch04">
    <table>
      <caption><?= $page_title ?></caption>
      <colgroup>
        <col width="15%">
        <col width="85%">
      </colgroup>
      <tbody>

        <tr>
          <th scope="row"><label for="title">제목</label></th>
          <td>
            <input type="text" class="frm_input" name="title" id="title" value="<?= $row['title'] ?? '' ?>" style="width:100%;">
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="subject_id">과목</label></th>
          <td>
            <select name="subject_id" id="subject_id" class="frm_input" style="width:200px;">
              <option value="">과목 선택</option>
            </select>
          </td>
        </tr>
        <tr>
          <th scope="row"><label for="report_date">시험일</label></th>
          <td>
            <input type="date" class="frm_input" name="report_date" id="report_date"
              value="<?= $row['report_date'] ?? '' ?>" style="width:150px;">
          </td>
        </tr>
        <tr>
          <th scope="row"><label>반 / 학생</label></th>
          <td>
            <select name="class_id" id="class_id" class="frm_input" style="width:150px; margin-right:10px;">
              <option value="">반 선택</option>
            </select>

            <select name="mb_id" id="mb_id" class="frm_input" style="width:150px;">
              <option value="">학생 선택</option>
            </select>
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="file">보고서 파일</label></th>
          <td>
            <?php if ($w === 'u' && !empty($file_row['bf_file'])) { ?>
              <div style="margin-bottom:5px;">
                현재 파일:
                <a href="<?= G5_DATA_URL ?>/file/<?= $bo_table ?>/<?= $file_row['bf_file'] ?>"
                  download="<?= get_text($file_row['bf_source']) ?>">
                  <?= get_text($file_row['bf_source']) ?>
                </a>
                <label style="margin-left:10px;">
                  <input type="checkbox" name="file_del" value="1">
                  파일 삭제
                </label>
              </div>
            <?php } ?>
            <input type="file" name="file" class="frm_input" id="file" style="width:400px;">
          </td>
        </tr>

        <tr>
          <th scope="row"><label for="content">내용</label></th>
          <td>
            <?php
            $content = '';
            if ($w === 'u' && isset($row['content'])) {
              $content = html_purifier($row['content']);
            }
            echo editor_html('content', $content, 0);
            ?>
          </td>
        </tr>

      </tbody>
    </table>
  </div>

  <div class="btn_fixed_top">
    <a href="./study_report_list.php" class="btn btn_02">목록</a>
    <?php if ($w === 'u') { ?>
      <button type="button" class="btn btn_01" id="btnDelete">삭제</button>
    <?php } ?>
    <button type="button" class="btn btn_submit" id="btnSubmit">
      <?= ($w === 'u') ? '수정' : '등록' ?>
    </button>
  </div>
</form>

<script src="<?= G5_API_URL ?>/api_study_report.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>
<script src="<?= G5_API_URL ?>/api_member.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>

<script>
  var selectedClassId = '<?= $row['class'] ?? '' ?>';
  var selectedStudentId = '<?= $row['mb_id'] ?? '' ?>';
  var selectedSubjectId = '<?= $row['subject_id'] ?? '' ?>';

  $(document).ready(function() {
    // 반 목록 로드
    loadClassList();

    // 과목 로드
    loadSubjectList();

    // 수정 모드일 때 제목, 반/학생 설정
    if ('<?= $w ?>' === 'u') {
      $('#title').val('<?= addslashes($row['title'] ?? '') ?>');

      if (selectedClassId) {
        setTimeout(function() {
          $('#class_id').val(selectedClassId).trigger('change');
        }, 500);
      }
    }

    // 반 선택 시 학생 목록 로드
    $('#class_id').on('change', function() {
      var classId = $(this).val();

      if (classId) {
        loadStudentList(classId).done(function() {
          if (selectedStudentId) {
            $('#mb_id').val(selectedStudentId);
          }
        });
      } else {
        $('#mb_id').html('<option value="">학생 선택</option>');
      }
    });

    // 등록/수정 버튼
    $('#btnSubmit').on('click', function() {
      submitReport();
    });

    // 삭제 버튼
    $('#btnDelete').on('click', function() {
      deleteReport();
    });
  });

  // 반 목록 불러오기
  function loadClassList() {
    apiClass.list(1, 100)
      .then(function(res) {
        const list = res.data || [];
        const $sel = $('#class_id');

        $sel.empty();
        $sel.append('<option value="">반 선택</option>');

        list.forEach(function(row) {
          const selected = (row.id == selectedClassId) ? 'selected' : '';
          $sel.append(`<option value="${row.id}" ${selected}>${row.name}</option>`);
        });
      })
      .fail(function(err) {
        console.warn("반 목록 로딩 실패", err);
      });
  }

  // 학생 목록 불러오기
  function loadStudentList(classId) {

    return memberAPI.list({
      mode: 'student', // 학생만
      field: 'class', // 'class' 컬럼 기준 검색
      keyword: classId, // 해당 class 값
      page: 1,
      rows: 200
    }).done(function(res) {

      if (res.result === 'SUCCESS') {
        const list = res.data.list || [];
        const $sel = $('#mb_id');

        $sel.empty();
        $sel.append('<option value="">학생 선택</option>');

        list.forEach(function(student) {
          const selected = (student.mb_id == selectedStudentId) ? 'selected' : '';
          $sel.append(`<option value="${student.mb_id}" ${selected}>${student.mb_name}</option>`);
        });
      }

    });
  }

  function loadSubjectList() {
    apiMockSubject.list(1, 200, '')
      .then(function(res) {
        const list = res.data.list || [];
        const $sel = $('#subject_id');

        $sel.empty();
        $sel.append('<option value="">과목 선택</option>');

        list.forEach(function(subj) {

          var text = subj.type + ' - ' + subj.subject_name;
          // 예) 일반과목 - 영어

          const selected = (subj.id == selectedSubjectId) ? 'selected' : '';

          $sel.append(`<option value="${subj.id}" ${selected}>${text}</option>`);
        });
      });
  }


  // 등록/수정 처리
  function submitReport() {
    // 에디터 값 textarea에 반영

    if (!validate()) return;

    var form = document.getElementById("report_form");
    var formData = new FormData(form);

    StudyReportAPI.submit(formData)
      .done(function(res) {
        alert('저장되었습니다.');
        location.href = './study_report_list.php';
      })
      .fail(function(res) {
        alert(res.message || '저장 실패');
      });
  }

  // 삭제 처리
  function deleteReport() {
    if (!confirm('정말 삭제하시겠습니까?')) return;

    StudyReportAPI.deleteWithFile('<?= $id ?>', '<?= $bo_table ?>')
      .done(function(res) {
        alert('삭제되었습니다.');
        location.href = './study_report_list.php';
      })
      .fail(function(res) {
        alert(res.message || '삭제 실패');
      });
  }

  function validate() {
    if (!$('#mb_id').val()) {
      alert('학생을 선택해주세요.');
      $('#mb_id').focus();
      return false;
    }

    if (!$('#title').val().trim()) {
      alert('제목을 입력해주세요.');
      $('#title').focus();
      return false;
    }
    // if (!$('#report_date').val()) {
    //   alert('시험일을 입력해주세요.');
    //   $('#report_date').focus();
    //   return false;
    // }

    return true;
  }
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>