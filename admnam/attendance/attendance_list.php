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

<script>
  $(function() {

    // 오늘 날짜
    function todayYmd() {
      let d = new Date();
      let m = ('0' + (d.getMonth() + 1)).slice(-2);
      let dd = ('0' + d.getDate()).slice(-2);
      return d.getFullYear() + '-' + m + '-' + dd;
    }


    // 리스트 로딩
    // 리스트 로딩 (신규 adminList 기반)
    function loadList() {

      var start_date = $('#start_date').val();
      var end_date = $('#end_date').val();

      if (!start_date || !end_date) {
        alert('기간을 선택하세요.');
        return;
      }

      AttendanceAPI.adminList({
          start_date: start_date,
          end_date: end_date,
          class_id: $('#class').val(),
          attend_type_id: $('#attend_type_id').val()
        })
        .then(function(res) {

          var list = res.data.list || [];
          var total = res.data.count || 0;

          $('#att_total').text(total);

          var $tbody = $('#att_list_body');
          $tbody.empty();

          if (list.length === 0) {
            $tbody.append('<tr><td colspan="6" class="empty_table">자료가 없습니다.</td></tr>');
            return;
          }

          list.forEach(function(row) {

            let isAttend = row.status === '출석완료';
            let displayDate = row.status === '출석완료' ? row.attend_dt?.substring(0, 16)  : row.date;

            let statusHtml = isAttend ?
              '출석완료' :
              '<span class="missing">미출석</span>';

            // ★ 상태에 따라 버튼 변경
            let checkBtn = '';

            if (isAttend) {
              // 출석완료 → 미출석 버튼
              checkBtn = `
                <button class="btn_01 btn_small btn_unattend"
                        data-id="${row.att_id}"
                        data-date="${row.date}">
                  미출석으로 변경
                </button>`;
            } else {
              // 미출석 → 출석 버튼
              checkBtn = `
                <button class="btn_01 btn_small btn_attend"
                        data-mb="${row.mb_id}"
                        data-type="${row.attend_type_id}"
                        data-date="${row.date}">
                  출석하기
                </button>`;
            }

            var tr = `
                <tr>
                  <td>${row.class_name || '-'}</td>
                  <td>${row.mb_name}</td>
                  <td>${row.attend_type_name || '-'}</td>
                  <td>${statusHtml}</td>
                  <td>${checkBtn}</td>
                  <td>${displayDate}</td>
                </tr>
                `;

            $tbody.append(tr);
          });


        })
        .fail(function() {
          $('#att_list_body').html(`<tr><td colspan="6" class="empty_table">조회 실패</td></tr>`);
          $('#att_total').text(0);
        });
    }


    // 기본 날짜: 오늘
    $('#start_date').val(todayYmd());
    $('#end_date').val(todayYmd());
    loadList();

    // 검색 버튼
    $('#btn_search').on('click', function() {
      loadList();
    });

    // 출석 버튼 처리
    $(document).on('click', '.btn_attend', function() {

      let mb_id = $(this).data('mb');
      let type_id = $(this).data('type');
      let date = $(this).data('date');

      if (!confirm('이 학생을 출석 처리하시겠습니까?')) return;

      let now = new Date();
      let hh = ('0' + now.getHours()).slice(-2);
      let mm = ('0' + now.getMinutes()).slice(-2);
      let ss = ('0' + now.getSeconds()).slice(-2);

      let attend_dt = `${date} ${hh}:${mm}:${ss}`;

      AttendanceAPI.add(mb_id, {
          attend_type_id: type_id,
          date: attend_dt,
          status: '출석완료'
        })
        .then(() => {
          alert('출석 처리되었습니다.');
          loadList();
        })
        .fail(() => alert('출석 처리 실패'));
    });

    $(document).on('click', '.btn_unattend', function() {

      let id = $(this).data('id'); // 출석 id
      let date = $(this).data('date');

      if (!confirm('해당 출석을 미출석으로 변경하시겠습니까?')) return;

      AttendanceAPI.update(id, {
          status: '미출석',
          attend_dt: date + ' 00:00:00'
        })
        .then(() => {
          alert('미출석으로 변경되었습니다.');
          loadList();
        })
        .fail(() => alert('변경 실패'));
    });

    loadClassList();
    // 반 목록 불러오기
    function loadClassList() {
      apiClass.list(1, 100) // 최대 100개까지 반 가져오기
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