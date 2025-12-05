<?php
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "모의고사 응시현황";
include_once('../head.php');

// ROLE 체크 (선생님만)
$role = $member['role'] ?? 'STUDENT';
$is_teacher = ($role === 'TEACHER');
if (!$is_teacher) {
  alert('선생님만 접근 가능한 화면입니다.', G5_VIEW_URL . "/index.php");
}
?>

<div class="common-section">


  <!-- ==========================
       상단 검색 영역
       ========================== -->
  <div class="common-form-box">

    <!-- 1줄: 시험선택 + 검색 -->
    <div class="common-form-row first-row">
      <div class="common-select-box" style="flex:1 1 auto;">
        <select id="mock_id" class="common-select">
          <option value="">시험 선택</option>
        </select>
      </div>
      <div class="common-select-box" style="flex:1 1 auto;">
        <select id="subject_id" class="common-select">
          <option value="">과목 선택</option>
        </select>
      </div>
      <div class="common-select-box" style="flex:1 1 auto;">
        <select id="class_id" class="common-select">
          <option value="">반 선택</option>
        </select>
      </div>
    </div>

    <!-- 2줄: 반 / 과목 -->
    <div class="common-form-row second-row">
      <div class="common-select-box" style="flex:1 1 auto;">
        <select id="status" class="common-select">
          <option value="">응시여부(전체)</option>
          <option value="COMPLETE">응시완료</option>
          <option value="INCOMPLETE">미응시</option>
        </select>
      </div>
      <!-- <div class="common-select-box" style="flex:1 1 auto;"> -->
      <span class="common-form-label" style="font-size:12px; margin-right:0; ">시험일</span>
      <input type="date" id="sdate" class="common-input-date">
      <input type="date" id="edate" class="common-input-date">
      <!-- </div> -->
    </div>


    <div class="common-form-row " style="justify-content:flex-end;">
      <button class="common-form-btn" id="btnExcel" style="width:auto;">엑셀다운로드</button>
      <button class="common-form-btn" id="btnInit" style="width:auto;">초기화</button>
      <button class="common-form-btn" id="btnSearch" style="width:auto;">검색</button>
    </div>

  </div>

  <!-- ==========================
       🔢 총계 영역
       ========================== -->
  <div id="summaryBox" style="margin-top:10px;margin-bottom:8px;font-size:13px;color:#ADABA6E0;">
    총계
    <span id="sum-complete" style="margin-left:8px;color:#E4C992E0;">응시완료 0건</span>
    <span style="margin:0 6px;">/</span>
    <span id="sum-incomplete" style="color:#ADABA6E0;">미응시 0건</span>
    <span style="margin-left:6px;font-size:12px;color:#8F8D88;">
      (전체 <span id="sum-total">0</span>건)
    </span>
  </div>

  <!-- ==========================
       리스트 영역
       ========================== -->
  <div class="common-list-container" id="applyList"></div>

  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" id="btnMore">더보기</button>
  </div>
</div>

<!-- ==========================
     API 스크립트
     ========================== -->
<script src="<?= G5_API_URL ?>/api_mock_apply.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_test.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<script>
  var page = 1;
  var rows = 20;
  var loading = false;

  $(function() {
    initFilters();
    bindEvents();
    runSearch(); // 첫 로딩
  });

  /* ==========================
       이벤트 바인딩
     ========================== */
  function bindEvents() {
    $('#btnSearch').on('click', function() {
      runSearch();
    });

    $('#btnMore').on('click', function() {
      if (loading) return;
      page++;
      loadList(false); // append
    });

    $('#btnInit').on('click', function() {
      // 셀렉트 박스 초기화
      $('#mock_id').val('');
      $('#subject_id').val('');
      $('#class_id').val('');
      $('#status').val('');

      // 날짜 초기화
      $('#sdate').val('');
      $('#edate').val('');

      // 검색 실행
      runSearch();
    });

    $('#btnExcel').on('click', function() {
      var params = getFilterParams();

      // 엑셀 타입 추가
      params.type = 'MOCK_APPLY_TEACHER_EXCEL';

      // GET 방식 다운로드 URL 생성
      // (POST 다운로드는 form-submit 방식이므로 GET 방식으로 구현)
      var query = Object.keys(params)
        .map(k => encodeURIComponent(k) + '=' + encodeURIComponent(params[k] || ''))
        .join('&');

      // 다운로드 실행
      window.location.href = g5_ctrl_url + '/ctrl_mock_apply.php?' + query;
    });

  }

  /* ==========================
       필터 초기 로딩
     ========================== */
  function initFilters() {
    loadMockTests();
    loadClasses();
    loadSubjects();
  }

  // 시험 목록
  function loadMockTests() {
    // (시그니처는 기존 api_mock_test.js에 맞게 조정 필요)
    apiMockTest.list(1, 200, '')
      .then(function(res) {
        var list = res.data.list || res.data || [];
        var $sel = $('#mock_id');
        $sel.empty().append('<option value="">시험 선택</option>');
        list.forEach(function(m) {
          $sel.append('<option value="' + m.id + '">' + m.name + '</option>');
        });
      });
  }

  // 반 목록
  function loadClasses() {
    apiClass.list(1, 200)
      .then(function(res) {
        var list = res.data.list || res.data || [];
        var $sel = $('#class_id');
        $sel.empty().append('<option value="">반 선택</option>');
        list.forEach(function(c) {
          $sel.append('<option value="' + c.id + '">' + c.name + '</option>');
        });
      });
  }

  // 과목 목록 (모의고사과목만)
  function loadSubjects() {
    apiMockSubject.list(1, 200, {
        'subject_type': '모의고사과목'
      })
      .then(function(res) {
        var list = res.data.list || res.data || [];
        var $sel = $('#subject_id');
        $sel.empty().append('<option value="">과목 선택</option>');
        list.forEach(function(s) {
          $sel.append('<option value="' + s.id + '">' + s.subject_name + '</option>');
        });
      });
  }

  /* ==========================
       검색 실행
     ========================== */
  function runSearch() {
    page = 1;
    $('#applyList').empty();
    $('#moreWrap').hide();

    // 1) 총계 먼저
    loadSummary();

    // 2) 리스트 로딩
    loadList(true);
  }

  /* ==========================
       필터 공통 수집
     ========================== */
  function getFilterParams() {
    return {
      mock_id: $('#mock_id').val(),
      class_id: $('#class_id').val(),
      subject_id: $('#subject_id').val(),
      status: $('#status').val(), // COMPLETE / INCOMPLETE / ''
      sdate: $('#sdate').val(),
      edate: $('#edate').val()
    };
  }

  /* ==========================
       🔢 총계 로딩 (페이징 X)
     ========================== */
  function loadSummary() {
    var params = getFilterParams();

    if (!apiMockApply.teacherSummary) {
      // 아직 API가 구현 안 된 경우 대비
      $('#sum-complete').text('응시완료 0건');
      $('#sum-incomplete').text('미응시 0건');
      $('#sum-total').text('0');
      return;
    }

    apiMockApply.teacherSummary(params)
      .then(function(res) {
        var data = res.data || {};
        var complete = data.total_complete || 0;
        var incomplete = data.total_incomplete || 0;
        var total = data.total || (complete + incomplete);

        $('#sum-complete').text('응시완료 ' + complete + '건');
        $('#sum-incomplete').text('미응시 ' + incomplete + '건');
        $('#sum-total').text(total);
      })
      .fail(function() {
        $('#sum-complete').text('응시완료 0건');
        $('#sum-incomplete').text('미응시 0건');
        $('#sum-total').text('0');
      });
  }

  /* ==========================
       리스트 로딩 (더보기)
     ========================== */
  function loadList(reset) {
    if (loading) return;
    loading = true;

    var params = getFilterParams();
    params.page = page;
    params.rows = rows;

    if (!apiMockApply.teacherList) {
      alert('teacherList API가 아직 구현되지 않았습니다.');
      loading = false;
      return;
    }

    if (reset) {
      $('#applyList').html('<p style="color:#aaa;padding:20px;">조회 중...</p>');
    }

    apiMockApply.teacherList(params)
      .then(function(res) {
        var data = res.data || {};
        var list = data.list || [];
        var total = data.total || 0;
        var p = data.page || page;
        var r = data.rows || rows;

        if (reset) {
          $('#applyList').empty();
        }

        if (!list.length && p === 1) {
          $('#applyList').html('<p style="color:#aaa;padding:20px;">검색된 데이터가 없습니다.</p>');
          $('#moreWrap').hide();
          loading = false;
          return;
        }

        list.forEach(function(row) {
          $('#applyList').append(renderApplyItem(row));
        });

        if (p * r < total) {
          $('#moreWrap').show();
        } else {
          $('#moreWrap').hide();
        }

        loading = false;
      })
      .fail(function() {
        if (reset) {
          $('#applyList').html('<p style="color:#aaa;padding:20px;">조회 중 오류가 발생했습니다.</p>');
        }
        loading = false;
      });
  }

  /* ==========================
       리스트 한 줄 템플릿
     ========================== */
  function renderApplyItem(row) {
    var mockName = row.mock_name || '';
    var subjectName = row.subject_name || '';
    var examDate = row.exam_date || '-';
    var className = row.class_name || '';
    var studentName = row.mb_name || '';

    // status: '신청' → 응시완료, 그 외/NULL → 미응시
    var isComplete = (row.status === '신청');
    var statusLabel = isComplete ? '응시완료' : '미응시';
    var statusClass = isComplete ? 'gold' : 'gray';

    return `
      <div class="mock-item-box">

        <div style="font-size:13px;color:#D9D8D5E0;margin-bottom:4px;">
          ${mockName}
          ${subjectName ? ' ' + subjectName : ''}
          <span style="font-size:12px;color:#ADABA6E0;">(시험일: ${examDate})</span>
        </div>

        <div style="display:flex;justify-content:space-between;align-items:center;font-size:13px;color:#E7E3DCB0;">
          <div>
            ${className ? className + ' ' : ''}${studentName}
          </div>

          <!-- 기존 mock-status-badge 재활용 (position:static 으로만 수정) -->
          <div class="mock-status-badge ${statusClass}" style="position:static;right:auto;top:auto;">
            ${statusLabel}
          </div>
        </div>

      </div>
    `;
  }
</script>

<?php include_once('../tail.php'); ?>