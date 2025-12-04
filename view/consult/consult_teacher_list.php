<?php
include_once('./_common.php');

$menu_group = 'consult';
$g5['title'] = "학과상담 (선생님)";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");

$role = $member['role'] ?? 'STUDENT';
if ($role !== 'TEACHER') alert("선생님만 접근 가능합니다.");
?>

<style>
  .teacher-slot-wrap {
    width: 90%;
    max-width: 420px;
    margin: 15px auto 30px;
  }

  .slot-item {
    display: flex;
    justify-content: space-between;
    align-items: center;
    background: #292A2F;
    padding: 10px 12px;
    border-radius: 8px;
    margin-bottom: 8px;
    font-size: 15px;
    color: #ffffffd0;
  }

  .slot-item.available {
    background: #32343A;
  }

  .slot-item.break {
    background: #3E3535;
  }

  .slot-item.block {
    background: #3A3A3A;
  }

  .slot-time {
    font-weight: 600;
    color: #fff;
  }

  .student-name {
    font-size: 14px;
    color: #E5C784;
  }

  .slot-cancel-btn {
    padding: 5px 10px;
    background: #4A4A4A;
    border-radius: 6px;
    color: #fff;
    font-size: 13px;
  }

  .mock-status-badge {
    display: inline-block;
    padding: 3px 7px;
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    margin-right: 6px;
  }

  .mock-status-badge.gold {
    background: #E5C784;
    color: #000;
  }

  .mock-status-badge.gray {
    background: #525252;
    color: #fff;
  }

  .mock-status-badge.break {
    background: #9A4D4D;
    color: #fff;
  }

  .mock-status-badge.block {
    background: #5A5A5A;
    color: #fff;
  }

  .date-select-wrap {
    width: 90%;
    max-width: 420px;
    margin: 15px auto 5px;
  }

  .common-input-date {
    width: 100%;
    padding: 10px 12px;
    border-radius: 8px;
    font-size: 15px;
    background: #292A2F;
    color: #fff;
    border: 1px solid #3A3A3A;
  }
</style>

<div class="date-select-wrap">
  <input type="date" id="selDate" class="common-input-date" />
</div>

<div class="teacher-slot-wrap" id="slotWrap"></div>

<script src="<?= G5_API_URL ?>/api_consult.js"></script>

<script>
  var teacherId = "<?= $mb_id ?>";

  $(document).ready(function () {
    const today = new Date().toISOString().substring(0, 10);
    $("#selDate").val(today);

    loadTimeSlots();

    $("#selDate").on("change", function() {
      loadTimeSlots();
    });
  });

  /* ======================================================
   * 슬롯 데이터 불러오기 (CONSULT_TEACHER_MY_LIST)
   * ====================================================== */
  function loadTimeSlots() {
    const date = $("#selDate").val();
    if (!date) return;

    ConsultAPI.teacherMyList(teacherId, '학과상담', date)
      .then(function(res) {
        const list = res.list || res.data?.list || [];
        renderSlots(list);
      })
      .fail(function(err) {
        console.log(err);
        $("#slotWrap").html(
          '<div style="color:#ADABA6E0;">데이터를 불러오지 못했습니다.</div>'
        );
      });
  }

  /* ======================================================
   * 시간 स्लॉट 렌더링
   * ====================================================== */
  function renderSlots(list) {
    let html = '';

    list.forEach(function(s) {

      let cls = 'slot-item';
      let badge = '';

      // status: 예약완료 / 상담가능 / 상담불가 / 휴게시간
      if (s.status === '예약완료') {
        badge = '<div class="mock-status-badge gold" style="position:static;">예약완료</div>';
      }
      else if (s.status === '휴게시간') {
        badge = '<div class="mock-status-badge break" style="position:static;">휴게시간</div>';
        cls += ' break';
      }
      else if (s.status === '상담불가') {
        badge = '<div class="mock-status-badge block" style="position:static;">상담불가</div>';
        cls += ' block';
      }
      else {
        badge = '<div class="mock-status-badge gray" style="position:static;">상담가능</div>';
        cls += ' available';
      }

      html += `
        <div class="${cls}">
          <div>
            <div class="slot-time">${s.time}</div>
            ${s.mb_name ? `<div class="student-name">${s.mb_name} 학생</div>` : ""}
          </div>
          <div>
            ${badge}
            ${s.status === '예약완료' ? `<button class="slot-cancel-btn" onclick="cancelConsult(${s.id})">취소</button>` : ""}
          </div>
        </div>
      `;
    });

    $("#slotWrap").html(html);
  }

  /* ======================================================
   * 상담 취소
   * ====================================================== */
  function cancelConsult(id) {
    if (!confirm("해당 상담을 취소하시겠습니까?")) return;

    ConsultAPI.cancel(id, teacherId, '학과상담')
      .then(function() {
        loadTimeSlots();
      })
      .fail(function(err) {
        console.log(err);
        alert("취소 중 오류가 발생했습니다.");
      });
  }
</script>

<?php include_once('../tail.php'); ?>
