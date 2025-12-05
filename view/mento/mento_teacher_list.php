<?php
include_once('./_common.php');

$menu_group = 'consult';
$g5['title'] = "멘토상담";
include_once('../head.php');

// 로그인 체크
$mb_id = $_SESSION['ss_mb_id'] ?? '';
if ($mb_id === '') alert("로그인이 필요합니다.");

$role = $member['role'] ?? 'STUDENT';
if ($role !== 'TEACHER') alert("선생님만 접근 가능합니다.", G5_VIEW_URL . "/index.php");
?>

<style>
</style>

<div class="wrap">
  <div style="margin-bottom:20px;">
    <button id="btnRefresh" class="consult-sheet-btn" style="flex:0 1 auto; height:30px;padding:0 12px;white-space:nowrap; font-size:12px; margin-right:20px;">
      새로 고침
    </button>
    <input type="date" id="selDate" class="common-input-date" />
  </div>

  <div class="teacher-slot-wrap" id="slotWrap"></div>
</div>

<script src="<?= G5_API_URL ?>/api_consult.js"></script>

<script>
  var teacherId = "<?= $mb_id ?>";

  $(document).ready(function() {
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

    ConsultAPI.teacherMyList(teacherId, '멘토상담', date)
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
   * 시간 렌더링
   * ====================================================== */
  function renderSlots(list) {
    let html = '';

    list.forEach(function(s) {

      let cls = 'common-list-box';
      let badge = '';

      // status: 예약완료 / 상담가능 / 상담불가 / 휴게시간
      if (s.status === '예약완료') {
        badge = '<div class="common-badge gold" style="position:static;">예약완료</div>';
      } else if (s.status === '휴게시간') {
        badge = '<div class="common-badge pink" style="position:static;">휴게시간</div>';
        cls += ' break';
      } else {
        badge = '<div class="common-badge gray" style="position:static;">상담가능</div>';
        cls += ' available';
      }

      html += `
        <div class="${cls}">
          <div>
            <div class="slot-time">${s.time} ${s.mb_name ? `${s.mb_name} (${s.class_name})` : "" }  </div>
          </div>
          <div style="display:flex; gap:3px;">
            ${s.status === '예약완료' ? `<button class="common-slot-cancel-btn" onclick="cancelConsult(${s.consult_id})">취소</button>` : ""}
            ${badge}
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

    ConsultAPI.cancel(id)
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