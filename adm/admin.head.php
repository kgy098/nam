<?
if (!defined('_GNUBOARD_')) {
  exit;
}

$g5_debug['php']['begin_time'] = $begin_time = get_microtime();

$files = glob(G5_ADMIN_PATH . '/css/admin_extend_*');
if (is_array($files)) {
  foreach ((array) $files as $k => $css_file) {

    $fileinfo = pathinfo($css_file);
    $ext = $fileinfo['extension'];

    if ($ext !== 'css') {
      continue;
    }

    $css_file = str_replace(G5_ADMIN_PATH, G5_ADMIN_URL, $css_file);
    add_stylesheet('<link rel="stylesheet" href="' . $css_file . '">', $k);
  }
}

require_once G5_PATH . '/head.sub.php';

function print_menu1($key, $no = '')
{
  global $menu;

  $str = print_menu2($key, $no);

  return $str;
}

function print_menu2($key, $no = '')
{
  global $menu, $auth_menu, $is_admin, $auth, $g5, $sub_menu;

  $str = "<ul>";
  for ($i = 1; $i < count($menu[$key]); $i++) {
    if (!isset($menu[$key][$i])) {
      continue;
    }

    if ($is_admin != 'super' && (!array_key_exists($menu[$key][$i][0], $auth) || !strstr($auth[$menu[$key][$i][0]], 'r'))) {
      continue;
    }

    $gnb_grp_div = $gnb_grp_style = '';

    if (isset($menu[$key][$i][4])) {
      if (($menu[$key][$i][4] == 1 && $gnb_grp_style == false) || ($menu[$key][$i][4] != 1 && $gnb_grp_style == true)) {
        $gnb_grp_div = 'gnb_grp_div';
      }

      if ($menu[$key][$i][4] == 1) {
        $gnb_grp_style = 'gnb_grp_style';
      }
    }

    $current_class = '';

    if ($menu[$key][$i][0] == $sub_menu) {
      $current_class = ' on';
    }

    $str .= '<li data-menu="' . $menu[$key][$i][0] . '"><a href="' . $menu[$key][$i][2] . '" class="gnb_2da ' . $gnb_grp_style . ' ' . $gnb_grp_div . $current_class . '">' . $menu[$key][$i][1] . '</a></li>';

    $auth_menu[$menu[$key][$i][0]] = $menu[$key][$i][1];
  }
  $str .= "</ul>";

  return $str;
}

$adm_menu_cookie = array(
  'container' => '',
  'gnb'       => '',
  'btn_gnb'   => '',
);

if (!empty($_COOKIE['g5_admin_btn_gnb'])) {
  $adm_menu_cookie['container'] = 'container-small';
  $adm_menu_cookie['gnb'] = 'gnb_small';
  $adm_menu_cookie['btn_gnb'] = 'btn_gnb_open';
}
?>

<style>

</style>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="<?= G5_ADMIN_URL ?>/css/admin_darkgold.css">

<script>
  var g5_model_url = "<?= G5_MODEL_URL ?>";
  var g5_api_url = "<?= G5_API_URL ?>";
  var g5_ctrl_url = "<?= G5_CTRL_URL ?>";
  var g5_view_url = "<?= G5_VIEW_URL ?>";

  var g5_admin_csrf_token_key = "<?= (function_exists('admin_csrf_token_key')) ? admin_csrf_token_key() : ''; ?>";
  var tempX = 0;
  var tempY = 0;

  function imageview(id, w, h) {

    menu(id);

    var el_id = document.getElementById(id);

    //submenu = eval(name+".style");
    submenu = el_id.style;
    submenu.left = tempX - (w + 11);
    submenu.top = tempY - (h / 2);

    selectBoxVisible();

    if (el_id.style.display != 'none')
      selectBoxHidden(id);
  }
</script>

<div id="to_content"><a href="#container">본문 바로가기</a></div>

<header id="hd">
  <h1><?= $config['cf_title'] ?></h1>
  <div id="hd_top">
    <!-- <button type="button" id="btn_gnb" class="btn_gnb_close <?= $adm_menu_cookie['btn_gnb']; ?>">메뉴</button> -->
    <div id="logo">
      <a href="<?= correct_goto_url(G5_ADMIN_URL); ?>">
        <img src="/theme/nam/img/nam/logo.png" alt="<?= get_text($config['cf_title']); ?> 관리자">
      </a>
    </div>
    <button type="button" id="btn_gnb" class="btn_gnb_close <?= $adm_menu_cookie['btn_gnb']; ?>">
      <i class="bi bi-list"></i>
    </button>
    <a class="btn btn_02 fold" id="fold">개발자메뉴</a>

    <div id="tnb">
      <ul>
        <? if (defined('G5_USE_SHOP') && G5_USE_SHOP) { ?>
          <!-- <li class="tnb_li"><a href="<?= G5_SHOP_URL ?>/" class="tnb_shop" target="_blank" title="쇼핑몰 바로가기">쇼핑몰 바로가기</a></li> -->
        <? } ?>
        <!-- <li class="tnb_li"><a href="<?= G5_URL ?>/" class="tnb_community" target="_blank" title="커뮤니티 바로가기">커뮤니티 바로가기</a></li>
        <li class="tnb_li"><a href="<?= G5_ADMIN_URL ?>/service.php" class="tnb_service">부가서비스</a></li> -->
        <li class="tnb_li"><button type="button" class="tnb_mb_btn">관리자<span class="./img/btn_gnb.png">메뉴열기</span></button>
          <ul class="tnb_mb_area">
            <li><a href="<?= G5_ADMIN_URL ?>/member_form.php?w=u&amp;mb_id=<?= $member['mb_id'] ?>">관리자정보</a></li>
            <li id="tnb_logout"><a href="<?= G5_BBS_URL ?>/logout.php">로그아웃</a></li>
          </ul>
        </li>
      </ul>
    </div>
  </div>


  <nav id="gnb" class="gnb_large <?= $adm_menu_cookie['gnb']; ?>">
    <h2>관리자 주메뉴</h2>
    <ul class="gnb_ul">
      <?
      $jj = 1;
      foreach ($amenu as $key => $value) {
        $href1 = $href2 = '';

        if (isset($menu['menu' . $key][0][2]) && $menu['menu' . $key][0][2]) {
          $href1 = '<a href="' . $menu['menu' . $key][0][2] . '" class="gnb_1da">';
          $href2 = '</a>';
        } else {
          continue;
        }

        $current_class = "";
        if (isset($sub_menu) && (substr($sub_menu, 0, 3) == substr($menu['menu' . $key][0][0], 0, 3))) {
          $current_class = " on";
        }

        $button_title = $menu['menu' . $key][0][1];
      ?>
        <li class="gnb_li<?= $current_class; ?> <?= $key >= 100 ? 'old' : '' ?>" >
          <span class="btn_op_2">
            <button type="button" class="btn_op menu-<?= $key; ?> menu-order-<?= $jj; ?>" title="<?= $button_title; ?>" style="width:5px;" ></button>
            <?= $button_title; ?>
          </span>
          <div class="gnb_oparea_wr">
            <div class="gnb_oparea">
              <h3><?= $menu['menu' . $key][0][1]; ?></h3>
              <?= print_menu1('menu' . $key, 1); ?>
            </div>
          </div>
        </li>
      <?
        $jj++;
      }     //end foreach
      ?>
    </ul>
  </nav>

</header>
<script>
  jQuery(function($) {
    $("#fold").on("click", function() {
      $(".gnb_li").each(function(idx, item) {
        if ($(this).hasClass("old") && $(this).hasClass("view")) {
          $(this).removeClass("view");
        } else if ($(this).hasClass("old") && !$(this).hasClass("view")) {
          $(this).addClass("view");
        }
      });
    });

    var menu_cookie_key = 'g5_admin_btn_gnb';

    $(".tnb_mb_btn").click(function() {
      $(".tnb_mb_area").toggle();
    });

    // $("#btn_gnb").click(function() {
    //   var $this = $(this);
    //   try {
    //     if (!$this.hasClass("btn_gnb_open")) {
    //       set_cookie(menu_cookie_key, 1, 60 * 60 * 24 * 365);
    //     } else {
    //       delete_cookie(menu_cookie_key);
    //     }
    //   } catch (err) {}

    //   $("#container").toggleClass("container-small");
    //   $("#gnb").toggleClass("gnb_small");
    //   $this.toggleClass("btn_gnb_open");
    // });

    // 상단 메뉴 접기 버튼
    $("#btn_gnb").click(function() {
      var $this = $(this);

      try {
        if (!$this.hasClass("btn_gnb_open")) {
          set_cookie(menu_cookie_key, 1, 60 * 60 * 24 * 365);
        } else {
          delete_cookie(menu_cookie_key);
        }
      } catch (err) {}

      $("#container").toggleClass("container-small");
      $("#gnb").toggleClass("gnb_small");
      $this.toggleClass("btn_gnb_open");

      // 🔽 아이콘 바꾸기
      var $icon = $this.find("i");
      if ($this.hasClass("btn_gnb_open")) {
        $icon.removeClass("bi-list").addClass("bi-x");
      } else {
        $icon.removeClass("bi-x").addClass("bi-list");
      }
    });

    // $(".gnb_ul li .btn_op").click(function() {
    //   $(this).parent().addClass("on").siblings().removeClass("on");
    // });
    $(".gnb_ul li ").click(function() {
      // $(this).parent().addClass("on").siblings().removeClass("on");
      $(this).addClass("on").siblings().removeClass("on");
    });

  });
</script>


<div id="wrapper">

  <div id="container" class="<?= $adm_menu_cookie['container']; ?>">

    <h1 id="container_title"><?= $g5['title'] ?></h1>
    <div class="container_wr">