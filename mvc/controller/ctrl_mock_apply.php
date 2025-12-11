<?php
include_once('./_common.php');

require_once G5_PATH . '/vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$type = $_POST['type'] ?? ($_GET['type'] ?? '');

switch ($type) {

  case AJAX_MOCK_APPLY_LIST:
    $list = select_mock_apply_list($_POST);
    echo json_encode(['result' => 'SUCCESS', 'data' => $list]);
    break;

  case 'MOCK_APPLY_TEACHER_SUMMARY':

    $mock_id    = trim($_POST['mock_id'] ?? '');
    $class_id   = trim($_POST['class_id'] ?? '');
    $subject_id = trim($_POST['subject_id'] ?? '');
    $status     = trim($_POST['status'] ?? '');
    $sdate      = trim($_POST['sdate'] ?? '');
    $edate      = trim($_POST['edate'] ?? '');

    // ✔ SELECT COUNT … (LEFT JOIN)
    $summary = select_mock_apply_teacher_summary(
      $mock_id,
      $class_id,
      $subject_id,
      $status,
      $sdate,
      $edate
    );

    jres(true, $summary);
    break;

  case 'MOCK_APPLY_TEACHER_LIST':
    $mock_id    = trim($_POST['mock_id'] ?? '');
    $class_id   = trim($_POST['class_id'] ?? '');
    $subject_id = trim($_POST['subject_id'] ?? '');
    $status     = trim($_POST['status'] ?? '');
    $sdate      = trim($_POST['sdate'] ?? '');
    $edate      = trim($_POST['edate'] ?? '');

    $page = max(1, (int)($_POST['page'] ?? 1));
    $rows = max(1, (int)($_POST['rows'] ?? 20));
    $start = ($page - 1) * $rows;

    $total = select_mock_apply_teacher_listcnt(
      $mock_id,
      $class_id,
      $subject_id,
      $status,
      $sdate,
      $edate
    );

    $list = select_mock_apply_teacher_list(
      $start,
      $rows,
      $mock_id,
      $class_id,
      $subject_id,
      $status,
      $sdate,
      $edate
    );

    jres(true, [
      'total' => $total,
      'page'  => $page,
      'rows'  => $rows,
      'list'  => $list
    ]);
    break;

  case AJAX_MOCK_APPLY_GET:
    $row = select_mock_apply_one($_POST['id']);
    echo json_encode(['result' => 'SUCCESS', 'data' => $row]);
    break;

  case AJAX_MOCK_APPLY_CREATE:
    $id = insert_mock_apply($_POST);
    echo json_encode(['result' => 'SUCCESS', 'id' => $id]);
    break;

  case AJAX_MOCK_APPLY_UPDATE:
    $id = $_POST['id'];
    $payload = $_POST;
    unset($payload['type'], $payload['id']);
    update_mock_apply($id, $payload);
    echo json_encode(['result' => 'SUCCESS']);
    break;

  case AJAX_MOCK_APPLY_DELETE:
    delete_mock_apply($_POST['id']);
    echo json_encode(['result' => 'SUCCESS']);
    break;
  case 'MOCK_APPLY_MY_LIST':
    $mb_id = $_SESSION['ss_mb_id'];
    $list = select_mock_apply_my_list($mb_id);
    echo json_encode(['result' => 'SUCCESS', 'data' => $list]);
    break;

  case 'MOCK_APPLY_MY_STATUS':
    $mock_id = $_POST['mock_id'];
    $mb_id   = $_SESSION['ss_mb_id'];
    $map = select_mock_apply_my_status($mock_id, $mb_id);
    echo json_encode(['result' => 'SUCCESS', 'data' => $map]);
    break;

  case 'MOCK_APPLY_TOGGLE':
    $mock_id    = $_POST['mock_id'];
    $subject_id = $_POST['subject_id'];
    $mb_id      = $_SESSION['ss_mb_id'];

    $res = toggle_mock_apply($mock_id, $subject_id, $mb_id);
    echo json_encode($res);
    break;

  case 'MOCK_APPLY_MY_OVERVIEW_LIST':

    $page = $_POST['page'] ?? 1;
    $rows = $_POST['rows'] ?? 5;
    $start = ($page - 1) * $rows;

    $mb_id = $member['mb_id'];

    $list  = select_mock_apply_my_overview_list($mb_id, $start, $rows);
    $total = select_mock_apply_my_overview_listcnt($mb_id);

    echo json_encode([
      'result' => 'SUCCESS',
      'data' => [
        'list'  => $list,
        'total' => $total,
        'page'  => $page,
        'rows'  => $rows
      ]
    ]);
    exit;

  case 'MOCK_APPLY_TEACHER_EXCEL':

    // GET 파라미터
    $mock_id    = trim($_GET['mock_id'] ?? '');
    $class_id   = trim($_GET['class_id'] ?? '');
    $subject_id = trim($_GET['subject_id'] ?? '');
    $status     = trim($_GET['status'] ?? '');
    $sdate      = trim($_GET['sdate'] ?? '');
    $edate      = trim($_GET['edate'] ?? '');

    // 전체 리스트 조회
    $list = select_mock_apply_teacher_list(
      0,
      999999,
      $mock_id,
      $class_id,
      $subject_id,
      $status,
      $sdate,
      $edate
    );

    // ================================
    // Spreadsheet 객체 생성
    // ================================
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // 제목 행
    $sheet->setCellValue('A1', '시험명');
    $sheet->setCellValue('B1', '시험일');
    $sheet->setCellValue('C1', '과목');
    $sheet->setCellValue('D1', '반');
    $sheet->setCellValue('E1', '학생명');
    $sheet->setCellValue('F1', '응시여부');

    // 내용 채우기
    $rowNum = 2;

    foreach ($list as $row) {

      $isComplete = ($row['status'] === '신청');
      $label = $isComplete ? '응시완료' : '미응시';

      $sheet->setCellValue("A{$rowNum}", $row['mock_name']);
      $sheet->setCellValue("B{$rowNum}", $row['exam_date']);
      $sheet->setCellValue("C{$rowNum}", $row['subject_name']);
      $sheet->setCellValue("D{$rowNum}", $row['class_name']);
      $sheet->setCellValue("E{$rowNum}", $row['mb_name']);
      $sheet->setCellValue("F{$rowNum}", $label);

      $rowNum++;
    }

    // ================================
    // 헤더 전송 (진짜 XLSX)
    // ================================
    $filename = "mock_apply_teacher_" . date('Ymd_His') . ".xlsx";

    header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    header("Cache-Control: max-age=0");
    header("Expires: 0");
    header("Pragma: public");

    // Writer 실행
    $writer = new Xlsx($spreadsheet);
    $writer->save('php://output');
    exit;


    // case 'MOCK_APPLY_TEACHER_EXCEL':

    //   // elog("excel: " . print_r($_REQUEST, true));

    //   // GET 기반
    //   $mock_id    = trim($_GET['mock_id'] ?? '');
    //   $class_id   = trim($_GET['class_id'] ?? '');
    //   $subject_id = trim($_GET['subject_id'] ?? '');
    //   $status     = trim($_GET['status'] ?? '');
    //   $sdate      = trim($_GET['sdate'] ?? '');
    //   $edate      = trim($_GET['edate'] ?? '');

    //   // ================================
    //   // 기존 리스트 조회 함수 그대로 사용
    //   // start = 0, rows = 매우 큰 값으로 전체 조회
    //   // ================================
    //   $start = 0;
    //   $rows  = 999999;

    //   $list = select_mock_apply_teacher_list(
    //     $start,
    //     $rows,
    //     $mock_id,
    //     $class_id,
    //     $subject_id,
    //     $status,
    //     $sdate,
    //     $edate
    //   );

    //   // ================================
    //   // 엑셀 다운로드 헤더
    //   // ================================
    //   header("Content-Type: application/vnd.ms-excel; charset=UTF-8");
    //   // header("Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
    //   header("Content-Disposition: attachment; filename=mock_apply_teacher_" . date('Ymd_His') . ".xls");
    //   // header("Content-Disposition: attachment; filename=\"mock_apply_teacher_" . date('Ymd_His') . ".xlsx\"");
    //   // header("Content-Transfer-Encoding: binary");
    //   header("Pragma: no-cache");
    //   header("Expires: 0");
    //   // header("Content-Description: PHP Generated Data");
    //   // echo "<meta http-equiv='Content-Type' content='text/html; charset=UTF-8' />";

    //   // ================================
    //   // 테이블 출력
    //   // ================================
    //   echo "<table border='1'>
    //           <tr>
    //             <th>시험명</th>
    //             <th>시험일</th>
    //             <th>과목</th>
    //             <th>반</th>
    //             <th>학생명</th>
    //             <th>응시여부</th>
    //           </tr>";

    //   foreach ($list as $row) {

    //     $isComplete = ($row['status'] === '신청');
    //     $label = $isComplete ? '응시완료' : '미응시';

    //     echo "<tr>";
    //     echo "<td>" . $row['mock_name'] . "</td>";
    //     echo "<td>" . $row['exam_date'] . "</td>";
    //     echo "<td>" . $row['subject_name'] . "</td>";
    //     echo "<td>" . $row['class_name'] . "</td>";
    //     echo "<td>" . $row['mb_name'] . "</td>";
    //     echo "<td>" . $label . "</td>";
    //     echo "</tr>";
    //   }

    //   echo "</table>";
    //   exit;


  default:
    echo json_encode(['result' => 'FAIL', 'msg' => 'Unknown type']);
    break;
}

exit;
