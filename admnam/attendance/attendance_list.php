<?php
$sub_menu = '040500';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '출결관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');
?>

<style>
  .missing {
    color: #e0558e;
    font-weight: 600;
  }
</style>


<script src="<?= G5_API_URL ?>/api_attendance.js"></script>
<script src="<?= G5_API_URL ?>/api_attendance_type.js"></script>
<script src="<?= G5_API_URL ?>/api_class.js"></script>

<div class="local_ov01 local_ov">
  <span class="btn_ov01">
    <span class="ov_txt">출결현황</span>
    <span class="ov_num">전체 <span id="att_total">0</span>건</span>
  </span>
</div>

<div class="local_sch01 local_sch">
  <form id="frmSearch" onsubmit="return false;">
    <div class="sch_last">

      <input type="date" id="start_date" class="frm_input" style="width:160px">
      ~
      <input type="date" id="end_date" class="frm_input" style="width:160px">

      <select id="class">
        <option value="">반선택</option>
      </select>

      <select id="attend_type_id">
        <option value="">출결구분선택</option>
      </select>

      <button type="button" class="btn_02" id="btn_search">검색</button>
    </div>
  </form>
  <div style="clear:both;"></div>
</div>

<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th>반</th>
        <th>학생이름</th>
        <th>출석구분</th>
        <th>출석여부</th>
        <th>출석체크</th>
        <th>출석일시</th>
      </tr>
    </thead>
    <tbody id="att_list_body">
      <tr>
        <td colspan="6" class="empty_table">검색 조건을 선택하세요.</td>
      </tr>
    </tbody>
  </table>
</div>

<div class="pg_wrap">
  <div id="pagination"></div>
</div>

<script>
  $(function() {

    var curPage = 1;
    var pageSize = 50;

    // 오늘 날짜
    function todayYmd() {
      let d = new Date();
      let m = ('0' + (d.getMonth() + 1)).slice(-2);
      let dd = ('0' + d.getDate()).slice(-2);
      return d.getFullYear() + '-' + m + '-' + dd;
    }

    // 페이지 UI
    function setPagination(total, currentPage) {
      var totalPage = Math.ceil(total / pageSize);
      if (totalPage < 1) totalPage = 1;

      var html = '';
      for (var i = 1; i <= totalPage; i++) {
        html += '<a href="#" class="pg_page ' + (i === currentPage ? 'on' : '') +
          '" data-page="' + i + '">' + i + '</a>';
      }
      $('#pagination').html(html);

      $(".pg_page").on("click", function(e) {
        e.preventDefault();
        loadList($(this).data("page"));
      });
    }

    // 리스트 로딩
    function loadList(page) {
      var start_date = $('#start_date').val();
      var end_date = $('#end_date').val();

      if (!start_date || !end_date) {
        alert('기간을 선택하세요.');
        return;
      }

      curPage = page;

      AttendanceAPI.statusList(start_date, end_date, {
        class: $('#class').val(),
        attend_type_id: $('#attend_type_id').val(),
        page: page,
        num: pageSize
      }).then(function(res) {
        var list = res.data || [];
        var total = res.total || 0;

        $('#att_total').text(total);

        var $tbody = $('#att_list_body');
        $tbody.empty();

        if (list.length === 0) {
          $tbody.append('<tr><td colspan="6" class="empty_table">자료가 없습니다.</td></tr>');
          return;
        }

        list.forEach(function(row) {

          // 출석 여부 및 시간 처리
          let isAttend = row.attend_dt ? true : false;

          let attendDate = isAttend ?
            row.attend_dt.substring(0, 16) :
            '-';

          let status = isAttend ?
            row.status :
            '<span class="missing">미출석</span>';

          // 출석 버튼 (미출석일 때만 표시)
          let checkBtn = !isAttend ?
            `<button class="btn_01 btn_attend btn_small" data-mb="${row.mb_id}" data-type="${row.attend_type_id}">출석</button>` :
            '';

          var tr = `
                <tr>
                  <td>${row.class || '-'}</td>
                  <td>${row.mb_name}</td>
                  <td>${row.attend_type_name || '-'}</td>
                  <td>${status}</td>
                  <td>${checkBtn}</td>
                  <td>${attendDate}</td>
                </tr>
              `;

          $tbody.append(tr);
        });


        setPagination(total, page);

      }).fail(function(err) {
        $('#att_list_body').html(
          '<tr><td colspan="6" class="empty_table">조회 실패</td></tr>'
        );
        $('#att_total').text(0);
        setPagination(0, 1);
      });
    }

    // 기본 날짜: 오늘
    $('#start_date').val(todayYmd());
    $('#end_date').val(todayYmd());
    loadList(1);

    // 검색 버튼
    $('#btn_search').on('click', function() {
      loadList(1);
    });

    // 출석 버튼 처리
    $(document).on('click', '.btn_attend', function() {

      let mb_id = $(this).data('mb');
      let attend_type_id = $(this).data('type');

      if (!confirm('이 학생을 출석 처리하시겠습니까?')) return;

      let now = new Date();
      let attend_dt = now.toISOString().slice(0, 19).replace('T', ' ');

      AttendanceAPI.add(mb_id, attend_dt, {
        attend_type_id: attend_type_id,
        status: '출석'
      }).then(function(res) {
        alert('출석 처리되었습니다.');
        loadList(curPage); // 현재 페이지 새로고침
      }).fail(function(err) {
        alert('출석 처리 실패');
      });

    });

    loadClassList();
    // 반 목록 불러오기
    function loadClassList() {
      ClassAPI.list(1, 100) // 최대 100개까지 반 가져오기
        .then(function(res) {
          const list = res.data || [];
          const $sel = $('#class');

          $sel.empty();
          $sel.append('<option value="">반선택</option>');

          list.forEach(function(row) {
            $sel.append(
              `<option value="${row.id}">${row.name}</option>`
            );
          });
        })
        .fail(function(err) {
          console.warn("반 목록 로딩 실패", err);
        });
    }

    loadAttendTypeList();

    function loadAttendTypeList() {
      AttendanceTypeAPI.list(1, 100) // 최대 100개까지 로딩
        .then(function(res) {
          const list = res.data.list || [];
          const $sel = $('#attend_type_id');

          // console.log("출결구분: " + JSON.stringify(res));

          $sel.empty();
          $sel.append('<option value="">출결구분선택</option>');

          list.forEach(function(row) {
            $sel.append(`<option value="${row.id}">${row.name}</option>`);
          });
        })
        .fail(function(err) {
          console.warn('출결구분 목록 로딩 실패', err);
        });
    }

  });
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>