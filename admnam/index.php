<?
$sub_menu = '010000';
require_once './_common.php';

@require_once './safe_check.php';

if (function_exists('social_log_file_delete')) {
  //소셜로그인 디버그 파일 24시간 지난것은 삭제
  social_log_file_delete(86400);
}

$g5['title'] = '관리자메인';
require_once './admin.head.php';

$new_member_rows = 5;
$new_point_rows = 5;
$new_write_rows = 5;

$addtional_content_before = run_replace('adm_index_addtional_content_before', '', $is_admin, $auth, $member);
if ($addtional_content_before) {
  echo $addtional_content_before;
}

if (!auth_check_menu($auth, '200100', 'r', true)) {
  $sql_common = " from {$g5['member_table']} ";

  $sql_search = " where (1) AND (role='STUDENT' OR role='TEACHER') ";

  if ($is_admin != 'super') {
    $sql_search .= " and mb_level <= '{$member['mb_level']}' ";
  }

  if (!$sst) {
    $sst = "mb_datetime";
    $sod = "desc";
  }

  $sql_order = " order by {$sst} {$sod} ";

  $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} {$sql_order} ";
  $row = sql_fetch($sql);
  $total_count = $row['cnt'];

  // 탈퇴회원수
  $sql = " select count(*) as cnt {$sql_common} {$sql_search} and mb_leave_date <> '' {$sql_order} ";
  $row = sql_fetch($sql);
  $leave_count = $row['cnt'];

  // 차단회원수
  $sql = " SELECT count(*) as cnt {$sql_common} {$sql_search} and mb_intercept_date <> '' {$sql_order} ";
  $row = sql_fetch($sql);
  $intercept_count = $row['cnt'];

  $sql = " SELECT * {$sql_common} {$sql_search} {$sql_order} limit {$new_member_rows} ";
  $result = sql_query($sql);

  $colspan = 12;

?>

  <section>
    <h2>신규가입회원 <?= $new_member_rows ?>건 목록</h2>
    <div class="local_desc02 ">
      총회원수 <?= number_format($total_count) ?>명 탈퇴 : <?= number_format($leave_count) ?>명
    </div>

    <div class="tbl_head01 tbl_wrap">
      <table>
        <caption>신규가입회원</caption>
        <thead>
          <tr>
            <th scope="col">이름</th>
            <th scope="col">회원구분</th>
            <th scope="col">전화번호</th>
            <th scope="col">등록일시</th>
          </tr>
        </thead>
        <tbody>
          <?
          for ($i = 0; $row = sql_fetch_array($result); $i++) {
            $role_str = "";
            if ( $row['role']==='STUDENT' ) {
              $role_str = "학생";
            } else if ( $row['role']==='TEACHER' ) {
              $role_str = "교사";
            }
          ?>
            <tr>
              <td class="td_mbid"><?= $row['mb_name'] ?></td>
              <td class="td_mbid"><?= $role_str ?></td>
              <td class="td_mbid"><?= $row['mb_hp'] ?></td>
              <td class="td_mbid"><?= $row['mb_datetime'] ?></td>
            </tr>
          <?
          }
          if ($i == 0) {
            echo '<tr><td colspan="' . $colspan . '" class="empty_table">자료가 없습니다.</td></tr>';
          }
          ?>
        </tbody>
      </table>
    </div>

    <div class="btn_list03 btn_list">
      <a href="./member/member_list.php">회원 전체보기</a>
    </div>
  </section>

<?
} //endif 최신 회원

$addtional_content_after = run_replace('adm_index_addtional_content_after', '', $is_admin, $auth, $member);
if ($addtional_content_after) {
  echo $addtional_content_after;
}
require_once './admin.tail.php';
