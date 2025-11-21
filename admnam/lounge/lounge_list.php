<?php
$sub_menu = '030100';
include_once('./_common.php');

auth_check_menu($auth, $sub_menu, 'r');

$g5['title'] = '스터디 라운지 관리';
include_once(G5_NAM_ADM_PATH . '/admin.head.php');

// 라운지 리스트
$list = select_lounge_list(0, 999);
$total_count = count($list);
?>

<script src="<?= G5_API_URL ?>/api_lounge.js"></script>
<script src="<?= G5_API_URL ?>/api_lounge_seat.js"></script>

<style>
  .seat-grid {
    border-collapse: collapse;
    margin-top: 15px;
  }

  .seat-grid td {
    width: 28px;
    height: 28px;
    border: 1px solid #ccc;
    padding: 0;
  }

  .seat-grid input {
    width: 100%;
    height: 100%;
    border: none;
    text-align: center;
    font-size: 11px;
    padding: 0;
  }

  .seat-header-wrap {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-top: 25px;
  }

  .seat-title {
    font-size: 17px;
    font-weight: bold;
  }

  .seat-guide {
    margin-top: 10px;
    color: #7d7d7d;
    font-size: 13px;
  }
</style>

<!-- ▣ 상단 개수 -->
<div class="local_ov01 local_ov">
  <span class="ov_txt">총 <?= number_format($total_count); ?>건</span>
</div>

<!-- ▣ 등록 버튼 -->
<div class="btn_add01 btn_add">
  <button type="button" id="btn-lounge-add" class="btn btn_01">라운지 등록</button>
</div>

<!-- ▣ 라운지 리스트 -->
<div class="tbl_head01 tbl_wrap">
  <table>
    <thead>
      <tr>
        <th style="width:60px;">No</th>
        <th>라운지명</th>
        <th style="width:180px;">관리</th>
      </tr>
    </thead>
    <tbody id="lounge-body">
      <?php if ($list) {
        $no = $total_count;
        foreach ($list as $row) { ?>
          <tr class="lounge-row" data-id="<?= $row['id']; ?>">
            <td><?= $no--; ?></td>
            <td><?= htmlspecialchars($row['name']); ?></td>
            <td>
              <button class="btn btn_02 btn-lounge-edit" data-id="<?= $row['id']; ?>">수정</button>
              <button class="btn btn_02 btn-lounge-del" data-id="<?= $row['id']; ?>">삭제</button>
            </td>
          </tr>
        <?php }
      } else { ?>
        <tr>
          <td colspan="3" class="empty_table">등록된 라운지가 없습니다.</td>
        </tr>
      <?php } ?>
    </tbody>
  </table>
</div>

<!-- ▣ 안내문구 -->
<div class="seat-guide">
  상단에서 <b>라운지</b>를 선택하고, 오른쪽의 <b>좌석배치 저장</b> 버튼을 클릭하세요.<br>
  빈칸은 복도입니다. 복도는 한칸으로 설정하세요.
</div>

<!-- ▣ 좌석배치 제목 + 저장 버튼 -->
<div class="seat-header-wrap">
  <h3 class="seat-title" id="seat-title">좌석 배치</h3>
  <button type="button" id="btn-seat-save" class="btn btn_01">좌석배치 저장</button>
</div>

<!-- ▣ 좌석 30×30 -->
<div id="seat-wrapper">
  <table class="seat-grid" id="seat-grid"></table>
</div>

<script>
  $(function() {

    /* -------------------------------------------------------
       1) 30×30 빈 input grid 생성 (cell_no = 1~900)
    ------------------------------------------------------- */
    function renderEmptyGrid() {
      var html = "";
      var cell = 1;

      for (var r = 0; r < 30; r++) {
        html += "<tr>";
        for (var c = 0; c < 30; c++) {
          html += `
          <td>
            <input type="text"
                   class="seat-input"
                   data-no="${cell}"
                   value=""
                   maxlength="10">
          </td>
        `;
          cell++;
        }
        html += "</tr>";
      }
      $("#seat-grid").html(html);
    }

    renderEmptyGrid();

    var currentLoungeId = null;
    var currentLoungeName = "";


    /* -------------------------------------------------------
       2) 라운지 클릭 → 해당 라운지 좌석 로딩
       cell_no 위치에 seat_no 값을 input.value 에 넣는다.
    ------------------------------------------------------- */
    $(document).on("click", ".lounge-row", function() {
      currentLoungeId = $(this).data("id");
      currentLoungeName = $(this).find("td:nth-child(2)").text();

      $("#seat-title").text(currentLoungeName + " 라운지 좌석배치");

      renderEmptyGrid();

      loungeSeatAPI.byLounge(currentLoungeId, {
          only_active: 1
        })
        .then(function(res) {
          var seats = res.data;

          seats.forEach(function(item) {
            var cell_no = item.cell_no;
            var seat_no = item.seat_no;

            $("#seat-grid").find("input[data-no='" + cell_no + "']").val(seat_no);
          });
        });
    });


    /* -------------------------------------------------------
       3) 좌석배치 저장
       - cell_no = grid index
       - seat_no = input value
    ------------------------------------------------------- */
    $("#btn-seat-save").on("click", function() {

      if (!currentLoungeId) {
        alert("먼저 라운지를 선택하세요.");
        return;
      }

      var msg = "⚠ 좌석배치를 수정하면 기존 예약 정보와 충돌할 수 있습니다.\n" +
        "좌석번호 변경/삭제 시 해당 좌석 예약이 무효가 될 수 있습니다.\n" +
        "정말 변경 내용을 저장하시겠습니까?";

      if (!confirm(msg)) return;

      // 입력된 좌석번호 수집
      var seats = [];
      $("#seat-grid .seat-input").each(function() {
        var seat_no = $.trim($(this).val());
        var cell_no = $(this).data("no");

        if (seat_no !== "") {
          seats.push({
            cell_no: cell_no,
            seat_no: seat_no
          });
        }
      });

      // 기존 좌석 전부 삭제 → 새로 저장
      loungeSeatAPI.byLounge(currentLoungeId)
        .then(function(res) {
          var old = res.data;
          var del = old.map(function(item) {
            return loungeSeatAPI.delete(item.id);
          });
          return $.when.apply($, del);
        })
        .then(function() {

          var add = seats.map(function(s) {
            return loungeSeatAPI.add({
              lounge_id: currentLoungeId,
              cell_no: s.cell_no,
              seat_no: s.seat_no,
              is_active: 1
            });
          });

          return $.when.apply($, add);
        })
        .then(function() {
          alert("좌석 배치가 저장되었습니다.");
        })
        .fail(function() {
          alert("저장 중 오류가 발생했습니다.");
        });
    });


    /* -------------------------------------------------------
       4) 화면 로딩 후 가장 최신 라운지 자동 선택
    ------------------------------------------------------- */
    setTimeout(function() {
      var $rows = $("#lounge-body .lounge-row");
      if ($rows.length > 0) $rows.eq(0).click();
    }, 300);


    /* -------------------------------------------------------
       5) 라운지 등록
    ------------------------------------------------------- */
    $("#btn-lounge-add").on("click", function() {
      var name = prompt("라운지명을 입력하세요.");
      if (!name) return;

      loungeAPI.add({
          name: name,
          is_active: 1
        })
        .then(function() {
          alert("등록되었습니다.");
          location.reload();
        })
        .fail(function() {
          alert("등록 실패");
        });
    });


    /* -------------------------------------------------------
       6) 라운지 수정
    ------------------------------------------------------- */
    $(document).on("click", ".btn-lounge-edit", function(e) {
      e.stopPropagation();

      var id = $(this).data("id");
      loungeAPI.get(id).then(function(res) {

        var name = prompt("라운지명 수정", res.data.name);
        if (!name) return;

        loungeAPI.update(id, {
            name: name
          })
          .then(function() {
            alert("수정되었습니다.");
            location.reload();
          });
      });
    });


    /* -------------------------------------------------------
       7) 라운지 삭제 (hard delete)
    ------------------------------------------------------- */
    $(document).on("click", ".btn-lounge-del", function(e) {
      e.stopPropagation();

      var id = $(this).data("id");
      var msg = "⚠ 라운지를 삭제하면 해당 라운지의 모든 좌석배치 정보도 함께 삭제됩니다.\n" +
        "삭제 후에는 복구할 수 없습니다.\n" +
        "정말 삭제하시겠습니까?";

      if (!confirm(msg)) return;

      loungeAPI.delete(id)
        .then(function() {
          alert("삭제되었습니다.");
          // location.reload();
        });
    });

  });
</script>

<?php
include_once(G5_NAM_ADM_PATH . '/admin.tail.php');
?>