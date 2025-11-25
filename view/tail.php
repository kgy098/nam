<?
if (!defined('_GNUBOARD_')) exit;
if (!isset($menu_group)) $menu_group = 'home';
?>

<nav class="bottom-nav">
  <a href="/view/index.php" class="nav-item <?= $menu_group=='home'?'active':'' ?>" data-target="home">
    <img class="icon off" src="/theme/nam/img/nam/ico/nav_home_off.png">
    <img class="icon on" src="/theme/nam/img/nam/ico/nav_home_on.png">
    <span>홈</span>
  </a>

  <a href="/view/att_main.php" class="nav-item <?= $menu_group=='att'?'active':'' ?>" data-target="att">
    <img class="icon off" src="/theme/nam/img/nam/ico/nav_att_off.png">
    <img class="icon on" src="/theme/nam/img/nam/ico/nav_att_on.png">
    <span>학습&출결</span>
  </a>

  <a href="/view/consult_main.php" class="nav-item <?= $menu_group=='consult'?'active':'' ?>" data-target="consult">
    <img class="icon off" src="/theme/nam/img/nam/ico/nav_consult_off.png">
    <img class="icon on" src="/theme/nam/img/nam/ico/nav_consult_on.png">
    <span>상담&예약</span>
  </a>

  <a href="/view/cal_main.php" class="nav-item <?= $menu_group=='cal'?'active':'' ?>" data-target="cal">
    <img class="icon off" src="/theme/nam/img/nam/ico/nav_cal_off.png">
    <img class="icon on" src="/theme/nam/img/nam/ico/nav_cal_on.png">
    <span>학사일정&공지</span>
  </a>

</nav>

<script>
  // ▼ 1. 현재 페이지 파일 이름 가져오기
  const currentPage = location.pathname.split('/').pop();  // ex: submain1.php
  // alert(currentPage);

  // ▼ 2. 해당 네비게이션 활성화
  $('.nav-item').each(function() {
    if ($(this).data('file') === currentPage) {
      $(this).addClass('active');
    }
  });

  // ▼ 3. 클릭 시 페이지 이동
  $('.nav-item').on('click', function(e) {
    e.preventDefault();
    location.href = $(this).attr('href');
  });
</script>

<? if ($is_admin == 'super') { ?>
  <!-- RUN TIME : <?= get_microtime() - $begin_time ?> -->
<? } ?>

</body>

</html>

<?= html_end() ?>