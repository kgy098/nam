<?php
include_once('../../common.php');

$filename = 'member_'.date('Ymd_His').'.xls';

header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=\"{$filename}\"");
header("Pragma: no-cache");
header("Expires: 0");

// UTF-8 BOM
echo "\xEF\xBB\xBF";

echo "<table border=\"1\">";
echo "<tr>
        <th>아이디</th>
        <th>이름</th>
        <th>반</th>
        <th>연락처</th>
        <th>이메일</th>
        <th>상품명</th>       <!-- 상품ID → 상품명 -->
        <th>상품금액</th>
        <th>입실일</th>
        <th>퇴실일</th>
      </tr>";

$list = select_member_excel_list($_REQUEST);

foreach ($list as $row) {

    echo "<tr>";
    echo "<td>{$row['mb_id']}</td>";
    echo "<td>{$row['mb_name']}</td>";
    echo "<td>{$row['class']}</td>";
    echo "<td>{$row['mb_hp']}</td>";
    echo "<td>{$row['mb_email']}</td>";

    echo "<td>{$row['product_name']}</td>";     // ★ 상품명 출력
    echo "<td>{$row['product_price']}</td>";

    echo "<td>{$row['join_date']}</td>";
    echo "<td>{$row['out_date']}</td>";
    echo "</tr>";
}

echo "</table>";

exit;
?>
