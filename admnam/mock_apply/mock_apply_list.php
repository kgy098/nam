<?php
include_once('./_common.php');

$sub_menu = '040320';
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
      <option value="신청">응시</option>
      <option value="취소">미응시</option>
    </select>

    <label>응시기간</label>
    <input type="date" id="sdate">
    ~
    <input type="date" id="edate">

    <button type="button" id="btnSearch" class="btn btn_02">검색</button>

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
        <th>응시일시</th>
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

    let rows = 20;
    let currentPage = 1;

    //=========================================================
    // 1) 시험 / 과목 / 반 옵션 로딩 (API 이용)
    //=========================================================

    apiMockTest.list({})
      .done(res => {
        let list = res?.data?.list ?? res?.data ?? [];
        list.forEach(m => {
          $('#mock_id').append(`<option value="${m.id}">${m.name}</option>`);
        });
      });

    apiMockSubject.list({})
      .done(res => {
        let list = res?.data?.list ?? res?.data ?? [];
        list.forEach(s => {
          $('#subject_id').append(`<option value="${s.id}">${s.subject_name}</option>`);
        });
      });

    apiClass.list({})
      .done(res => {
        let list = res?.data?.list ?? res?.data ?? [];
        list.forEach(c => {
          $('#class_id').append(`<option value="${c.id}">${c.name}</option>`);
        });
      });


    //=========================================================
    // 2) 리스트 로딩 (API만 사용)
    //=========================================================
    function loadList(page) {
      currentPage = page;

      let params = {
        page: page,
        rows: rows,
        mock_id: $('#mock_id').val(),
        subject_id: $('#subject_id').val(),
        class_id: $('#class_id').val(),
        status: $('#status').val(),
        sdate: $('#sdate').val(),
        edate: $('#edate').val()
      };

      $('#apply-tbody').html(
        `<tr><td colspan="6" class="empty_table">조회중...</td></tr>`
      );

      // ⭐ API 사용 (절대 $.ajax 사용하지 않음!)
      apiMockApply.list(params)
        .done(res => {

          let list = res?.data?.list ?? res?.data ?? [];
          let total = res?.data?.total ?? 0;

          if (list.length === 0) {
            $('#apply-tbody').html(
              `<tr><td colspan="6" class="empty_table">검색된 데이터가 없습니다.</td></tr>`
            );
            $('#paging_area').html('');
            $('#total_count').text(0);
            return;
          }

          // 리스트 UI 생성
          let html = '';
          list.forEach(row => {
            html += `
                    <tr>
                        <td>${row.mock_name ?? ''}</td>
                        <td>${row.subject_name ?? ''}</td>
                        <td>${row.class_name ?? ''}</td>
                        <td>${row.mb_name ?? ''}</td>
                        <td>${row.status === '신청' ? '응시' : '미응시'}</td>
                        <td>${row.exam_date ?? ''}</td>
                    </tr>
                    `;
          });

          $('#apply-tbody').html(html);

          $('#total_count').text(total);

          buildPaging(total, page);
        });
    }

    //=========================================================
    // 3) 페이징
    //=========================================================
    function buildPaging(total, page) {
      let totalPage = Math.ceil(total / rows);
      let html = '';

      if (totalPage <= 1) {
        $('#paging_area').html('');
        return;
      }

      if (page > 1)
        html += `<a href="#" class="pg_page" data-page="${page-1}">이전</a> `;

      for (let i = 1; i <= totalPage; i++) {
        if (i === page)
          html += `<strong class="pg_current">${i}</strong> `;
        else
          html += `<a href="#" class="pg_page" data-page="${i}">${i}</a> `;
      }

      if (page < totalPage)
        html += `<a href="#" class="pg_page" data-page="${page+1}">다음</a> `;

      $('#paging_area').html(html);
    }

    $(document).on('click', '.pg_page', function(e) {
      e.preventDefault();
      loadList($(this).data('page'));
    });

    // 검색 버튼
    $('#btnSearch').click(function() {
      loadList(1);
    });

    // 첫 로딩
    loadList(1);

  });
</script>


<?php
include_once(G5_ADMIN_PATH . '/admin.tail.php');
?>