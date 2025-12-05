<?
$sub_menu = '010200';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'w');

$g5['title'] = '교사등록';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

// 파람
$w     = $_REQUEST['w'] ?? '';
$mb_id = $_REQUEST['mb_id'] ?? '';  // 교사도 mb_id 기준

$defaults = get_member_form_defaults();
$db_row   = [];

// 등록 / 수정 분기
if ($w === '' || $w === 'w') {
  $row = $defaults;
} else if ($w === 'u' && $mb_id !== '') {
  $db_row = select_member_one_by_id($mb_id);
  if (!$db_row) alert("교사 정보를 찾을 수 없습니다.");
  $row = array_merge($defaults, $db_row);
} else {
  alert("잘못된 요청입니다.");
}
?>

<script src="<?= G5_API_URL ?>/api_member.js"></script>
<script src="<?= G5_API_URL ?>/api_teacher_time_block.js"></script>

<style>
  /* 슬롯 UI 스타일 */
  .slot-container {
    margin-top: 15px;
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 6px;
  }

  .slot-btn {
    padding: 8px 5px;
    text-align: center;
    border: 1px solid #ccc;
    background: #fafafa;
    cursor: pointer;
    border-radius: 4px;
    font-size: 13px;
  }

  .slot-btn.reserved {
    background: #e1e8ff;
    border-color: #7b8aff;
  }

  .slot-btn.break {
    background: #ffe1e1;
    border-color: #ff7b7b;
  }

  .slot-btn:hover {
    background: #f0f0f0;
  }

  .slot-legend {
    margin: 10px 0;
    font-size: 13px;

  }

  .slot-legend span {
    display: inline-block;
    margin-right: 10px;
    padding: 2px 6px;
    border-radius: 3px;
    font-size: 12px;
  }

  .legend-break {
    background: #ffe1e1;
    border: 1px solid #ff7b7b;
  }

  .legend-reserved {
    background: #e1e8ff;
    border: 1px solid #7b8aff;
  }
</style>

<form name="t_form" id="t_form" method="post" autocomplete="off">
  <input type="hidden" name="w" value="<?= $w ?>">
  <input type="hidden" name="mb_id" value="<?= $mb_id ?>">
  <input type="hidden" name="role" value="TEACHER">

  <div class="tbl_frm01 tbl_wrap local_sch04">
    <table>
      <caption><?= $g5['title'] ?></caption>
      <tbody>
        <tr>
          <th class="required">이름</th>
          <td><input type="text" name="mb_name" class="frm_input" value="<?= $row['mb_name'] ?>"></td>
          <th class="required">전화번호</th>
          <td><input type="text" name="mb_hp" class="frm_input" value="<?= $row['mb_hp'] ?>"></td>
        </tr>

        <tr>
          <th>이메일</th>
          <td><input type="text" name="mb_email" class="frm_input" value="<?= $row['mb_email'] ?>"></td>
          <th>주소</th>
          <td><input type="text" name="mb_addr1" class="frm_input" value="<?= $row['mb_addr1'] ?>"></td>
        </tr>

        <tr>
          <th class="required">인증번호</th>
          <td colspan="3">
            <div style="display:flex; gap:10px; align-items:center;">
              <input type="text" name="auth_no" class="frm_input" value="<?= $row['auth_no'] ?>" placeholder="8자리" style="width:200px;">
              <?php if ($w === 'u') { ?>
                <button type="button" class="btn btn_01">문자발송</button>
              <?php } else { ?>
                <button type="button" class="btn btn_01" disabled style="opacity:0.5; cursor:not-allowed;">문자발송</button>
              <?php } ?>
              <span style="color:#777; font-size:12px;">문자발송은 교사등록 시 자동으로 발송됩니다.</span>
            </div>
          </td>
        </tr>

        <tr>
          <th>입사일</th>
          <td><input type="date" name="join_date" class="frm_input" value="<?= $row['join_date'] ?>"></td>
          <th>퇴사일</th>
          <td><input type="date" name="out_date" class="frm_input" value="<?= $row['out_date'] ?>"></td>
        </tr>

      </tbody>
    </table>
  </div>

  <!-- ============================================= -->
  <!--  휴일/휴게시간 설정 영역 추가 -->
  <!-- ============================================= -->
  <?php if ($w === 'u') { ?>
    <!-- ============================================= -->
    <!--  휴일/휴게시간 설정 (수정시에만 표시) -->
    <!-- ============================================= -->

    <h3 style="margin-top:30px;">휴일 / 휴게시간 설정</h3>

    <div class="tbl_frm01 tbl_wrap">
      <table>
        <tbody>

          <tr>
            <th>날짜 선택</th>
            <td colspan="3">

              <input type="date" id="t_date" class="frm_input">

              <button type="button" class="btn btn_02" onclick="setHoliday()">휴일 설정</button>
              <button type="button" class="btn btn_02" onclick="loadSlots()">새로 고침</button>

            </td>
          </tr>

          <tr>
            <th>시간 슬롯</th>
            <td colspan="3">
              <div class="slot-legend">
                <span class="legend-break" style="color:#000 !important;">휴게시간</span>
                <span class="legend-reserved" style="color:#000 !important;">상담예약됨</span>
              </div>

              <div id="slotContainer" class="slot-container" style="color:#000 !important;"></div>
            </td>
          </tr>

        </tbody>
      </table>
    </div>
  <?php } ?>


  <div class="btn_fixed_top">
    <a href="./member_list.php?mode=teacher" class="btn btn_02">목록</a>
    <button type="button" class="btn_submit btn" onclick="saveTeacher()">저장</button>
  </div>

</form>

<script>
  $(document).ready(function() {

    <?php if ($w === 'u') { ?>
      var today = new Date().toISOString().slice(0, 10);
      $("#t_date").val(today);

      // 수정 진입 시 오늘 날짜 슬롯 자동 로딩
      loadSlots();

      // 날짜 변경 시 자동 슬롯 로딩
      $(document).on("change", "#t_date", function() {
        loadSlots();
      });
    <?php } ?>

  });

  // ============================
  // 선택된 날짜
  // ============================
  function getSelectedDate() {
    return $("#t_date").val();
  }

  // ============================
  // 슬롯 불러오기
  // ============================
  function loadSlots() {
    var mb_id = $("input[name='mb_id']").val();
    var date = getSelectedDate();

    if (!mb_id || !date) return;

    TeacherTimeBlockAPI.slots({
      mb_id: mb_id,
      target_date: date
    }).then(function(res) {
      renderSlots(res.data);
    });
  }

  // ============================
  // 슬롯 렌더링 (상담/휴게 표시 포함)
  // ============================
  function renderSlots(slots) {
    var box = $("#slotContainer");
    box.empty();

    $.each(slots, function(_, s) {
      var cls = "slot-btn";
      var label = s.time;

      // console.log(s.time + " # " + s.status)
      // 휴게시간
      if (s.is_break) {
        cls += " break";
        label += "<br><span style='font-size:11px;color:#000 !important;'>휴게시간</span>";
      }
      // 상담 
      if (s.is_reserved) {
        cls += " reserved";

        var typeTxt = (s.consult_type === '학과상담') ? "학과상담" : "멘토상담";
        var rTxt =
          typeTxt + " " +
          (s.class_name ? (s.class_name) : "") +
          (s.mb_name ? (s.mb_name + " ") : "")
        label += `<br><span style='font-size:11px;color:#000 !important;'>${rTxt}</span>`;
      }

      var $div = $('<div class="' + cls + '" >' + label + '</div>');

      // 클릭 → 휴게시간 설정
      $div.on("click", function() {
        setBreakSlot(s);
      });

      box.append($div);
    });
  }

  // ============================
  // 개별 휴게 설정
  // ============================
  function setBreakSlot(slot) {
    let date = getSelectedDate();
    let mb_id = $("input[name='mb_id']").val();
    let next = getNextTime(slot.time);

    if (!next) return;

    // ======================
    // 1) 휴게 → 일반슬롯 (BREAK 삭제)
    // ======================
    if (slot.is_break === true) {

      if (!slot.break_id) {
        alert("휴게시간 정보가 없습니다.");
        return;
      }

      if (confirm("해당 시간의 휴게시간을 해제하시겠습니까?")) {
        TeacherTimeBlockAPI.remove(slot.break_id)
          .then(loadSlots);
      }

      return;
    }

    // ======================
    // 2) 일반 → 휴게 (BREAK 추가)
    // ======================
    function saveBreak() {
      TeacherTimeBlockAPI.add({
        mb_id: mb_id,
        target_date: date,
        start_time: slot.time,
        end_time: next,
        ttb_type: 'BREAK'
      }).then(loadSlots);
    }

    // 상담예약 있는 경우 확인
    if (slot.is_reserved === true) {
      if (confirm("해당 시간에 상담예약이 있습니다.\n그래도 휴게시간으로 설정하시겠습니까?")) {
        saveBreak();
      }
    } else {
      saveBreak();
    }
  }


  // ============================
  // 휴일 (전체 BREAK)
  // ============================
  function setHoliday() {
    var date = getSelectedDate();
    var mb_id = $("input[name='mb_id']").val();

    if (!date) return;

    TeacherTimeBlockAPI.slots({
      mb_id: mb_id,
      target_date: date
    }).then(function(res) {

      var slots = res.data;
      var hasRes = false;

      $.each(slots, function(_, s) {
        if (s.exists) {
          hasRes = true;
          return false;
        }
      });

      if (hasRes) {
        if (!confirm("해당 날짜에 상담예약이 있습니다.\n그래도 휴일(전체 휴게)로 설정하시겠습니까?")) {
          return;
        }
      }

      // 전체 30분 단위 BREAK 등록
      $.each(slots, function(_, s) {
        var next = getNextTime(s.time);
        if (!next) return;

        TeacherTimeBlockAPI.add({
          mb_id: mb_id,
          target_date: date,
          start_time: s.time,
          end_time: next,
          ttb_type: 'BREAK'
        });
      });

      alert("휴일 설정이 완료되었습니다.");
      loadSlots();
    });
  }

  // ============================
  // 30분 후 시각 계산
  // ============================
  function getNextTime(t) {
    var parts = t.split(":");
    var h = parseInt(parts[0], 10);
    var m = parseInt(parts[1], 10);

    m += 30;
    if (m >= 60) {
      h++;
      m -= 60;
    }

    if (h >= 23 && m > 0) return null;

    var hh = (h < 10 ? "0" + h : "" + h);
    var mm = (m < 10 ? "0" + m : "" + m);
    return hh + ":" + mm;
  }

  // ============================
  // 교사 저장
  // ============================
  function saveTeacher() {
    if (!validateTeacherForm()) return;

    var data = $("#t_form").serialize();
    var w = $("input[name='w']").val();

    $.post(
      g5_ctrl_url + "/ctrl_member.php",
      data + "&type=MEMBER_CHECK_DUP",
      function(res) {
        if (res.data && res.data.duplicate) {
          alert("동일 이름/전화번호 교사가 이미 존재합니다.");
          return;
        }

        if (w === 'u') {
          memberAPI.update(data).then(afterSave);
        } else {
          memberAPI.create(data).then(afterSave);
        }
      },
      'json'
    );
  }

  function afterSave(res) {
    if (res.result === 'SUCCESS') {
      alert('저장되었습니다.');
      location.href = './member_list.php?mode=teacher';
    } else {
      alert('저장 실패: ' + (res.data || '오류'));
    }
  }

  function validateTeacherForm() {
    if ($("input[name='mb_name']").val().trim() === '') {
      alert("이름은 필수 항목입니다.");
      return false;
    }
    if ($("input[name='mb_hp']").val().trim() === '') {
      alert("전화번호는 필수 항목입니다.");
      return false;
    }
    if ($("input[name='auth_no']").val().trim() === '') {
      alert("인증번호는 필수 항목입니다.");
      return false;
    }
    return true;
  }
</script>

<?
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>