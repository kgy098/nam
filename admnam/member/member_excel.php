<?php
include_once('../../common.php');      // 경로는 프로젝트에 맞게

$filename = 'member_'.date('Ymd_His').'.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");

echo "\xEF\xBB\xBF";

echo "<table border=\"1\">";
echo "<tr>
        <th>아이디</th>
        <th>이름</th>
        <th>역할</th>
        <th>반</th>
        <th>연락처</th>
        <th>이메일</th>
        <th>상품ID</th>
        <th>상품금액</th>
        <th>선납금</th>
        <th>잔금</th>
        <th>가입일</th>
        <th>탈퇴일</th>
      </tr>";

$list = select_member_excel_list($_REQUEST);

foreach ($list as $row) {
    echo "<tr>";
    echo "<td>{$row['mb_id']}</td>";
    echo "<td>{$row['mb_name']}</td>";
    echo "<td>{$row['role']}</td>";
    echo "<td>{$row['class']}</td>";
    echo "<td>{$row['mb_hp']}</td>";
    echo "<td>{$row['mb_email']}</td>";
    echo "<td>{$row['product_id']}</td>";
    echo "<td>{$row['product_price']}</td>";
    echo "<td>{$row['product_price_first']}</td>";
    echo "<td>{$row['product_price_last']}</td>";
    echo "<td>{$row['mb_datetime']}</td>";
    echo "<td>{$row['mb_leave_date']}</td>";
    echo "</tr>";
}

echo "</table>";
exit;
