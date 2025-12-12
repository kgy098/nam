<?php
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "출결관리";
include_once('../head.php');

// 로그인 정보
$mb_id = $_SESSION['ss_mb_id'] ?? '';
?>

<div class="wrap">

  <!-- 출결구분 필터 -->
  <div class="common-section">
    <div class="common-form-row first-row">
      <div class="common-select-box">
        <select id="attendTypeFilter" class="common-select">
          <option value="">출결구분 선택</option>
        </select>
      </div>
    </div>
  </div>

  <!-- 리스트 -->
  <div class="common-list-container" id="attList"></div>

  <!-- 더보기 -->
  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>
</div>

<script src="<?= G5_API_URL ?>/api_attendance.js"></script>
<script src="<?= G5_API_URL ?>/api_attendance_type.js"></script>

<script>
  /*************************************************
   * 출결관리 화면 JS (attendance_list.php 전용)
   *************************************************/
  var mb_id = "<?= $mb_id ?>";
  var page = 1;
  var loading = false;

  var attendTypes = []; // 출결구분 리스트

  /*************************************************
   * 초기 실행
   *************************************************/
  $(document).ready(function() {
    loadAttendTypes();
    loadOverviewList();
  });

  /*************************************************
   * 출결구분 로드
   *************************************************/
  function loadAttendTypes() {
    AttendanceTypeAPI.list(1, 100)
      .then(res => {
        attendTypes = res.data.list || [];
        renderTypeSelect();
      })
      .fail(() => alert("출결구분을 불러오지 못했습니다."));
  }

  function renderTypeSelect() {
    var sel = $("#attendTypeFilter");
    sel.append(attendTypes.map(t =>
      `<option value="${t.id}">${t.name}</option>`
    ));
  }

  /*************************************************
   * 오버뷰 리스트 조회 + 더보기
   *************************************************/
  function loadOverviewList() {
    if (loading) return;
    loading = true;

    AttendanceAPI.overviewList(page, 5, mb_id)
      .then(res => {
        var list = res.data.list || [];

        // console.log("overviewList: " + JSON.stringify(res));

        renderList(list);

        // 더보기 버튼 표시 여부
        if (list.length === 5 * attendTypes.length) {
          $("#moreWrap").show();
        } else {
          $("#moreWrap").hide();
        }

        page++;
        loading = false;
      })
      .fail(() => {
        alert("출결 정보를 불러오지 못했습니다.");
        loading = false;
      });
  }

  function loadMore() {
    loadOverviewList();
  }

  /*************************************************
   * 리스트 렌더링
   *************************************************/
  function renderList(list) {
    var wrap = $("#attList");

    list.forEach(function(item) {

      var date = item.date; // YYYY-MM-DD
      var typeName = item.attend_type_name; // 출결구분명
      var status = item.status; // 출석완료 / 미출석
      var statusClass = (status === '출석완료') ? 'gold' : 'pink';

      var idKey = date + "_" + item.attend_type_id;

      var html = `
      <div class="mock-item-box attendance-item-box">

        <!-- 날짜 + 출결구분 + 상태 -->
        <div class="mock-title-row" onclick="toggleDetail('${idKey}')">
            <div class="common-title">
                ${formatKoreanDate(date)}
                <span style="margin-left:6px; color:#666;">${typeName}</span>
            </div>

            <span class="mock-status-badge ${statusClass}">
                ${status}
            </span>
        </div>

        <!-- 아래 설명 영역 -->
        <div class="mock-meta">
            ${date} ${typeName}
        </div>

        <!-- arrow -->
        <div style="text-align:right; margin-top:6px;">
            <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png"
                 class="common-arrow"
                 id="arrow-${idKey}"
                 onclick="toggleDetail('${idKey}'); event.stopPropagation();">
        </div>

        <!-- 상세 영역 -->
        <div id="detail-${idKey}" class="mock-subject-list" style="display:none;">
          ${
            status === '출석완료'
            ? `
              <div class="attendance-done-box">
                  <div class="status-badge status-green">출석 완료된 항목입니다.</div>
              </div>
            `
            : `
              <div class="attendance-auth-box">
                  <div class="common-row">
                    <span class="common-row-label">인증번호</span>
                    <input type="tel"
                           class="common-input"
                           id="auth-${idKey}"
                           placeholder="8자리 입력">
                  </div>

                  <div class="common-row" style="margin-top:10px;">
                    <button class="common-btn"
                            onclick="doAttend('${idKey}', '${item.attend_type_id}', '${date}')">
                      출석하기
                    </button>
                  </div>
              </div>
            `
          }
        </div>

      </div>
    `;

      wrap.append(html);
    });
  }


  /*************************************************
   * 상세 펼침
   *************************************************/
  function toggleDetail(idKey) {
    var box = $("#detail-" + idKey);
    var arrow = $("#arrow-" + idKey);

    if (box.is(":visible")) {
      box.slideUp(180);
      arrow.removeClass("open");
    } else {
      box.slideDown(200);
      arrow.addClass("open");
    }
  }

  /*************************************************
   * 출석하기
   *************************************************/
  function doAttend(idKey, typeId, date) {
    var auth = $("#auth-" + idKey).val().trim();
    if (auth.length < 4) {
      alert("인증번호를 정확히 입력해주세요.");
      return;
    }

    let now = new Date();
    let hh = ('0' + now.getHours()).slice(-2);
    let mm = ('0' + now.getMinutes()).slice(-2);
    let ss = ('0' + now.getSeconds()).slice(-2);
    let attend_dt = `${date} ${hh}:${mm}:${ss}`;


    // 서버 insert_attendance는 attend_dt = NOW() 자동처리
    AttendanceAPI.add(mb_id, {
        attend_type_id: typeId,
        date: date,
        status: '출석완료'
      })
      .then(() => {
        alert("출석 완료되었습니다.");
        location.reload();
      })
      .fail(() => alert("출석에 실패했습니다."));
  }

  /*************************************************
   * 출결구분 이름 찾기(백업)
   *************************************************/
  function getTypeName(id) {
    var t = attendTypes.find(x => x.id == id);
    return t ? t.name : "";
  }
</script>

<?php include_once('../tail.php'); ?>