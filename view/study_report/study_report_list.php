<?php
include_once('./_common.php');
$menu_group = 'att';
$g5['title'] = "학습보고서";
include_once('../head.php');

// STUDENT / TEACHER
$role = $member['role'] ?? 'STUDENT';
$is_teacher = ($role === 'TEACHER');
?>

<div class="common-section">

  <!-- ==========================
        상단 검색 영역
     ========================== -->
  <div class="common-form-box">

    <!-- 1줄차 -->
    <div class="common-form-row first-row">
      <div class="common-select-box">
        <select id="subject" class="common-select">
          <option value="">과목선택</option>
        </select>
      </div>

      <button class="common-form-btn" onclick="searchReport()">검색</button>
    </div>

    <!-- 2줄차 -->
    <div class="common-form-row second-row">
      <span class="common-form-label">시험일</span>

      <input type="date" id="start_date" class="common-input-date">
      <input type="date" id="end_date" class="common-input-date">
    </div>

  </div>


  <!-- ==========================
        리스트 영역
     ========================== -->
  <div class="common-list-container" id="reportList"></div>

  <div class="common-more-wrap" id="moreWrap" style="display:none;">
    <button class="common-more-btn" onclick="loadMore()">더보기</button>
  </div>


  <!-- ==========================
       선생 업로드 영역
     ========================== -->
  <?php if ($is_teacher) { ?>
    <div style="width:90%;max-width:420px;margin:40px auto 0;">

      <div style="font-size:17px;font-weight:600;color:#ffffffd0;margin-bottom:10px;">
        학습보고서 업로드
      </div>

      <div class="common-form-box">

        <!-- 반 선택 -->
        <div class="common-form-row first-row">
          <div class="common-select-box">
            <select id="class_id" class="common-select">
              <option value="">반 선택</option>
            </select>
          </div>
        </div>

        <!-- 학생 선택 -->
        <div class="common-form-row">
          <div class="common-select-box">
            <select id="student_id" class="common-select">
              <option value="">학생 선택</option>
            </select>
          </div>
        </div>

        <!-- 과목 선택 -->
        <div class="common-form-row">
          <div class="common-select-box">
            <select id="subject_id" class="common-select">
              <option value="">과목 선택</option>
            </select>
          </div>
        </div>

        <div class="common-form-row">
          <span class="common-form-label">시험일</span>
          <input type="date" id="report_date" class="common-input-date">
        </div>

        <!-- 제목 -->
        <div class="common-form-row">
          <span class="common-form-label">제목</span>
          <input type="text" id="title" class="common-input" placeholder="제목 입력">
        </div>

        <!-- 내용 -->
        <div class="common-form-row">
          <span class="common-form-label">내용</span>
          <textarea id="content" class="common-input"
            style="height:120px;padding-top:12px;" placeholder="내용 입력"></textarea>
        </div>

        <!-- 파일첨부 -->
        <div class="common-form-row">
          <span class="common-form-label">파일첨부</span>
          <input type="file" id="file" class="common-input-file">
        </div>

        <!-- 업로드 버튼 -->
        <div class="common-form-row">
          <button class="common-btn" onclick="uploadReport()">학습보고서 업로드</button>
        </div>

      </div>
    </div>
  <?php } ?>

</div>


<!-- ==========================
     API
     ========================== -->
<script src="<?= G5_API_URL ?>/api_mock_subject.js"></script>
<script src="<?= G5_API_URL ?>/api_study_report.js"></script>
<script src="<?= G5_API_URL ?>/api_member.js"></script>

<?php if ($is_teacher) { ?>
  <script src="<?= G5_API_URL ?>/api_class.js"></script>
<?php } ?>


<script>
  var page = 1;
  var loading = false;
  var PAGE_SIZE = <?= $is_teacher ? 3 : 5 ?>;

  // 초기 로딩
  loadSubjectsTo('#subject');

  <?php if ($is_teacher) { ?>
    loadClasses();
    loadSubjectsTo('#subject_id');
  <?php } ?>

  loadReports();


  /* ==========================
       과목 전체 로딩(공통)
     ========================== */
  function loadSubjectsTo(selectId, selectedValue = '') {
    apiMockSubject.list(1, 200, '')
      .then(function(res) {
        const list = res.data.list || [];
        const $sel = $(selectId);

        $sel.empty().append('<option value="">과목 선택</option>');

        list.forEach(function(subj) {
          var text = subj.type + ' - ' + subj.subject_name;
          const sel = (selectedValue == subj.id) ? 'selected' : '';
          $sel.append(`<option value="${subj.id}" ${sel}>${text}</option>`);
        });
      });
  }


  /* ==========================
       반 / 학생 목록 (선생용)
     ========================== */
  <?php if ($is_teacher) { ?>

    function loadClasses() {
      apiClass.list(1, 200).then(function(res) {
        (res.data || []).forEach(function(c) {
          $("#class_id").append(`<option value="${c.id}">${c.name}</option>`);
        });
      });
    }

    $("#class_id").on("change", function() {
      const classId = $(this).val();
      $("#student_id").empty().append('<option value="">학생 선택</option>');
      if (classId) loadStudentList(classId);
    });

    function loadStudentList(classId) {
      return memberAPI.list({
        mode: 'student',
        field: 'class',
        keyword: classId,
        page: 1,
        rows: 200
      }).done(function(res) {
        const list = res.data.list || [];
        const $sel = $('#student_id');

        $sel.empty().append('<option value="">학생 선택</option>');

        list.forEach(function(st) {
          $sel.append(`<option value="${st.mb_id}">${st.mb_name}</option>`);
        });
      });
    }

  <?php } ?>


  /* ==========================
       검색
     ========================== */
  function searchReport() {
    page = 1;
    $("#reportList").empty();
    loadReports();
  }


  /* ==========================
       리스트 조회
     ========================== */
  function loadReports() {
    if (loading) return;
    loading = true;

    const filters = {
      subject_id: $("#subject").val(),
      date_from: $("#start_date").val(),
      date_to: $("#end_date").val()
    };

    const apiCall = <?= $is_teacher ? "StudyReportAPI.list" : "StudyReportAPI.myList" ?>;

    apiCall(page, PAGE_SIZE, filters)
      .then(function(res) {
        const list = res.data.list || [];
        const total = res.data.total;
        const pageN = res.data.page;
        const rows = res.data.rows;

        if (list.length === 0 && pageN === 1) {
          $("#reportList").append('<p style="color:#aaa;padding:20px;">등록된 학습보고서가 없습니다.</p>');
          $("#moreWrap").hide();
          loading = false;
          return;
        }

        list.forEach(function(r) {
          $("#reportList").append(makeReportItem(r));
        });

        if (pageN * rows < total) $("#moreWrap").show();
        else $("#moreWrap").hide();

        loading = false;
      })
      .fail(function() {
        alert("조회 오류가 발생했습니다.");
        loading = false;
      });
  }

  function loadMore() {
    page++;
    loadReports();
  }


  /* ==========================
       리스트 템플릿
     ========================== */
  function makeReportItem(r) {
    const subjectLabel = ((r.type ?? '') + ' ' + (r.subject_name ?? '')).trim();
    const isMine = ("<?= $member['mb_id'] ?>" == r.reg_id);

    return `
      <div class="common-item" style="position:relative;">

        <div class="common-item-row">
          <div class="common-info">
            <div class="common-title">${r.title}</div>
            <div class="common-meta">
              과목: ${subjectLabel}<br>
              시험일시: ${r.report_date ?? '-'}
            </div>
          </div>
        </div>

                ${isMine ? `
        <button class="common-btn btn-danger"
           onclick="deleteReport(${r.id}); event.stopPropagation();"
           style="position:absolute; right:10px; top:35px; width:70px; height:30px; font-size:13px;">삭제</button>
        ` : ''}


        <div style="text-align:right; margin-top:6px;" onclick="toggleDetail(${r.id})">
          <img src="<?= G5_THEME_IMG_URL ?>/nam/ico/down.png" class="common-arrow" id="arrow-${r.id}">
        </div>

       

        <div class="common-detail" id="detail-${r.id}">
          <div class="common-desc">

            <div style="white-space:pre-line;margin-bottom:12px;">
              ${r.content ?? ''}
            </div>

            ${
              r.result_image
                ? `
                  <div style="text-align:right;margin-bottom:12px;">
                    <button class="common-form-btn"
                        style="width:auto; padding:6px 10px; font-size:13px;"
                        onclick="downloadFile('${r.result_image}', '${r.file_name}')">
                      파일 다운로드
                    </button>
                  </div>
                  <img src="${r.result_image}" style="width:100%;border-radius:8px;">
                `
                : `<div style="padding:20px;text-align:center;color:#aaa;">결과 이미지 없음</div>`
            }
          </div>
        </div>

      </div>`;
  }


  /* ==========================
       삭제
     ========================== */
  function deleteReport(id) {
    if (!confirm("해당 학습보고서를 삭제하시겠습니까?")) return;

    StudyReportAPI.remove(id)
      .then(function() {
        alert("삭제되었습니다.");
        page = 1;
        $("#reportList").empty();
        loadReports();
      })
      .fail(function() {
        alert("삭제 중 오류가 발생했습니다.");
      });
  }


  async function downloadFile(url, name) {
    const ok = await appConfirm('다운로드 받으시겠습니까?');

    if ( ok ) {
      var a = document.createElement("a");
      a.href = url;
      a.download = name || "download";
      document.body.appendChild(a);
      a.click();
      a.remove();
    }
  }

  /* ==========================
       상세 토글
     ========================== */
  function toggleDetail(id) {
    $("#detail-" + id).toggleClass("open");
  }


  /* ==========================
       📌 선생 업로드: CREATE → FILE_UPLOAD 2단계
     ========================== */
  <?php if ($is_teacher) { ?>

    function uploadReport() {
      var classId = $("#class_id").val();
      var studentId = $("#student_id").val();
      var subjectId = $("#subject_id").val();
      var title = $("#title").val().trim();
      var content = $("#content").val().trim();
      var file = $("#file")[0].files[0];

      if (!classId) return alert("반을 선택해주세요.");
      if (!studentId) return alert("학생을 선택해주세요.");
      if (!subjectId) return alert("과목을 선택해주세요.");
      if (!title) return alert("제목을 입력해주세요.");
      if (!content) return alert("내용을 입력해주세요.");

      // STEP 1: 보고서 생성
      $.ajax({
          url: g5_ctrl_url + "/ctrl_study_report.php",
          type: "POST",
          data: {
            type: "STUDY_REPORT_CREATE",
            mb_id: studentId,
            subject_id: subjectId,
            report_date: $("#report_date").val(),
            title: title,
            content: content
          },
          dataType: "json"
        })
        .then(function(res) {
          if (!res.result || !res.data) throw new Error("보고서 생성 실패");

          var newId = res.data.id; // 생성된 id

          // 파일 없으면 여기서 끝
          if (!file) {
            afterUploadSuccess();
            return;
          }

          // STEP 2: 파일 업로드
          var fd = new FormData();
          fd.append("type", "STUDY_REPORT_FILE_UPLOAD");
          fd.append("wr_id", newId);
          fd.append("file", file);

          return $.ajax({
            url: g5_ctrl_url + "/ctrl_study_report.php",
            type: "POST",
            data: fd,
            processData: false,
            contentType: false,
            dataType: "json"
          });
        })
        .then(function() {
          afterUploadSuccess();
        })
        .fail(function(err) {
          alert("업로드 중 오류가 발생했습니다.");
        });

    }

    function afterUploadSuccess() {
      alert("학습보고서가 업로드되었습니다.");
      $("#title").val("");
      $("#content").val("");
      $("#file").val("");
      page = 1;
      $("#reportList").empty();
      loadReports();
    }

  <?php } ?>
</script>

<?php include_once('../tail.php'); ?>