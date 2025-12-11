<?php
include_once('./_common.php');

$sub_menu = '040300';
auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '모의고사 응시현황';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<script src="<?= G5_API_URL ?>/api_mock_apply.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_test.js"></script>
<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<style>
  .local_sch select,
  .local_sch input {
    margin-right: 5px;
  }
</style>


<!-- =============================== -->
<!-- 검색 영역 -->
<!-- =============================== -->
<div class="local_sch local_sch01">
  <form id="fsearch" onsubmit="return false;">

    <label>시험선택</label>
    <select id="mock_id">
      <option value="">전체</option>
    </select>

    <label>과목</label>
    <select id="subject_id">
      <option value="">전체</option>
    </select>

    <label>반</label>
    <select id="class_id">
      <option value="">전체</option>
    </select>

    <label>응시여부</label>
    <select id="status">
      <option value="">전체</option>
      <option value="COMPLETE">응시완료</option>
      <option value="INCOMPLETE">미응시</option>
    </select>

    <label>시험일</label>
    <input type="date" id="sdate">
    ~
    <input type="date" id="edate">

    <button type="button" id="btnSearch" class="btn btn_02">검색</button>
    <button type="button" id="btnReset" class="btn btn_02">초기화</button>
    <button type="button" id="btnExcel" class="btn btn_02">엑셀다운로드</button>

  </form>
</div>

<div class="local_ov01 local_ov">
  <span class="ov_txt">총 <span id="total_count">0</span>건</span>
</div>

<!-- 리스트 -->
<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th>모의고사</th>
        <th>과목</th>
        <th>반</th>
        <th>학생이름</th>
        <th>응시여부</th>
        <th>시험일</th>
      </tr>
    </thead>
    <tbody id="apply-tbody">
      <tr>
        <td colspan="6" class="empty_table">데이터를 불러오는 중...</td>
      </tr>
    </tbody>
  </table>
</div>

<!-- 페이징 -->
<div class="local_frm01">
  <div id="paging_area" style="text-align:center; margin-top:10px;"></div>
</div>


<script>
  jQuery(function($) {

    var rows = 20;
    var currentPage = 1;

    // ============================
    // 공통 필터 파라미터 수집
    // ============================
    function getFilterParams() {
      return {
        page: currentPage,
        rows: rows,
        mock_id: $('#mock_id').val(),
        subject_id: $('#subject_id').val(),
        class_id: $('#class_id').val(),
        status: $('#status').val(),   // '', COMPLETE, INCOMPLETE
        sdate: $('#sdate').val(),
        edate: $('#edate').val()
      };
    }

    //=========================================================
    // 1) 시험 / 과목 / 반 옵션 로딩 (API 이용)
    //=========================================================

    // 시험 목록
    apiMockTest.list({})
      .done(function(res) {
        var list = res && res.data && (res.data.list || res.data) || [];
        list.forEach(function(m) {
          $('#mock_id').append('<option value="' + m.id + '">' + (m.name || '') + '</option>');
        });
      });

    // 과목 목록 (모의고사과목만)
    apiMockSubject.list({ subject_type: '모의고사과목' })
      .done(function(res) {
        var list = res && res.data && (res.data.list || res.data) || [];
        list.forEach(function(s) {
          $('#subject_id').append('<option value="' + s.id + '">' + (s.subject_name || '') + '</option>');
        });
      });

    // 반 목록
    apiClass.list({})
      .done(function(res) {
        var list = res && res.data && (res.data.list || res.data) || [];
        list.forEach(function(c) {
          $('#class_id').append('<option value="' + c.id + '">' + (c.name || '') + '</option>');
        });
      });

    //=========================================================
    // 2) 리스트 로딩 (선생님용 teacherList API 사용)
    //=========================================================
    function loadList(page) {
      currentPage = page || 1;

      var params = getFilterParams();
      params.page = currentPage;

      $('#apply-tbody').html(
        '<tr><td colspan="6" class="empty_table">조회중...</td></tr>'
      );

      // ⭐ 선생님용 응시현황 API (앱과 동일) ⭐
      if (!apiMockApply.teacherList) {
        $('#apply-tbody').html(
          '<tr><td colspan="6" class="empty_table">teacherList API가 정의되어 있지 않습니다.</td></tr>'
        );
        $('#paging_area').html('');
        $('#total_count').text('0');
        return;
      }

      apiMockApply.teacherList(params)
        .done(function(res) {

          var data = res && res.data || {};
          var list = data.list || data || [];
          var total = data.total || 0;

          if (!list.length) {
            $('#apply-tbody').html(
              '<tr><td colspan="6" class="empty_table">검색된 데이터가 없습니다.</td></tr>'
            );
            $('#paging_area').html('');
            $('#total_count').text('0');
            return;
          }

          var html = '';
          list.forEach(function(row) {

            var mockName    = row.mock_name   || '';
            var subjectName = row.subject_name || '';
            var className   = row.class_name  || '';
            var studentName = row.mb_name     || '';
            var examDate    = row.exam_date   || '';

            var isComplete  = (row.status === '신청'); // CRUD와 동일 기준
            var statusLabel = isComplete ? '응시완료' : '미응시';

            html += '<tr>' +
              '<td>' + mockName + '</td>' +
              '<td>' + subjectName + '</td>' +
              '<td>' + className + '</td>' +
              '<td>' + studentName + '</td>' +
              '<td>' + statusLabel + '</td>' +
              '<td>' + examDate + '</td>' +
              '</tr>';
          });

          $('#apply-tbody').html(html);
          $('#total_count').text(total);

          buildPaging(total, currentPage);
        })
        .fail(function() {
          $('#apply-tbody').html(
            '<tr><td colspan="6" class="empty_table">데이터 조회 중 오류가 발생했습니다.</td></tr>'
          );
          $('#paging_area').html('');
          $('#total_count').text('0');
        });
    }

    //=========================================================
    // 3) 페이징
    //=========================================================
    function buildPaging(total, page) {
      var totalPage = Math.ceil(total / rows);
      var html = '';

      if (!totalPage || totalPage <= 1) {
        $('#paging_area').html('');
        return;
      }

      if (page > 1) {
        html += '<a href="#" class="pg_page" data-page="' + (page - 1) + '">이전</a> ';
      }

      for (var i = 1; i <= totalPage; i++) {
        if (i === page) {
          html += '<strong class="pg_current">' + i + '</strong> ';
        } else {
          html += '<a href="#" class="pg_page" data-page="' + i + '">' + i + '</a> ';
        }
      }

      if (page < totalPage) {
        html += '<a href="#" class="pg_page" data-page="' + (page + 1) + '">다음</a> ';
      }

      $('#paging_area').html(html);
    }

    $(document).on('click', '.pg_page', function(e) {
      e.preventDefault();
      var p = parseInt($(this).data('page'), 10) || 1;
      loadList(p);
    });

    //=========================================================
    // 4) 버튼 이벤트
    //=========================================================

    // 검색 버튼
    $('#btnSearch').on('click', function() {
      loadList(1);
    });

    // 초기화 버튼
    $('#btnReset').on('click', function() {
      $('#mock_id').val('');
      $('#subject_id').val('');
      $('#class_id').val('');
      $('#status').val('');
      $('#sdate').val('');
      $('#edate').val('');
      loadList(1);
    });

    // 엑셀다운로드 버튼
    $('#btnExcel').on('click', function() {

      if (typeof g5_ctrl_url === 'undefined') {
        alert('g5_ctrl_url 이 정의되어 있지 않습니다.');
        return;
      }

      var params = getFilterParams();
      params.type = 'MOCK_APPLY_TEACHER_EXCEL';

      var query = Object.keys(params)
        .map(function(k) {
          return encodeURIComponent(k) + '=' + encodeURIComponent(params[k] || '');
        })
        .join('&');

      window.location.href = g5_ctrl_url + '/ctrl_mock_apply.php?' + query;
    });

    // 첫 로딩
    loadList(1);

  });
</script>


<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
?>
