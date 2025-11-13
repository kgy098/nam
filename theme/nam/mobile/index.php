<?php
if (!defined('_GNUBOARD_')) exit; // 개별 페이지 접근 불가

if (G5_COMMUNITY_USE === false) {
  include_once(G5_THEME_MSHOP_PATH . '/index.php');
  return;
}

include_once(G5_THEME_MOBILE_PATH . '/head.php');
?>

<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/main.min.css" rel="stylesheet" />
<style>
  .wrap {
    padding: 16px
  }

  #cal {
    max-width: 1200px;
    margin: 0 auto
  }

  .fc {
    --fc-page-bg-color: #0f1113;
    --fc-neutral-bg-color: #14161a;
    --fc-border-color: #1f232a;
    --fc-text-color: #d7d7d7;
    --fc-event-bg-color: #d4af37;
    --fc-event-border-color: #d4af37
  }

  .fc .fc-toolbar-title {
    font-size: 1.25rem;
    font-weight: 700;
    color: #fff
  }

  .fc .fc-button {
    background: #1b1f25;
    border: 0
  }

  .fc .fc-button:hover {
    filter: brightness(1.15)
  }

  .fc-daygrid-day-number {
    color: #fff
  }

  .fc-day-today {
    background: #171a20
  }

  .fc .gold-tag {
    display: inline-block;
    padding: 2px 6px;
    border-radius: 10px;
    background: #2a2720;
    color: #d4af37;
    font-size: 11px
  }

  .fc-day-selected {
    position: relative
  }

  .fc-day-selected .fc-daygrid-day-number {
    color: #111
  }

  .fc-day-selected::after {
    content: "";
    position: absolute;
    inset: 6px;
    border-radius: 999px;
    background: linear-gradient(145deg, #f7d57a, #d4af37);
    z-index: -1
  }
</style>

<script defer src="<?= G5_URL ?>/js/fullcalendar/index.global.min.js"></script>


<div class="main">
  <div class="app-bar">
    <div class="frame"><img class="img" src="img/menu.svg" /></div>
    <div class="div"><img class="logo" src="<?= NAM_IMG_URL ?>/logo.png" /> </div>
  </div>

  <div class="frame-wrapper">
    <div class="frame-2">


      <div class="wrap">
        <div id="cal"></div>
      </div>


      <!-- <div class="frame-3">
        <div class="frame-4">
          <div class="december">2025년 10월</div>
          <img class="ico-down" src="img/down-2.svg" />
        </div>
        <div class="frame-5"><img class="img" src="img/left.svg" /> <img class="img" src="img/right.svg" /></div>
      </div>
      <div class="frame-6">
        <div class="frame-7">
          <div class="s-copy">일</div>
          <div class="text-wrapper">월</div>
          <div class="text-wrapper">화</div>
          <div class="text-wrapper">수</div>
          <div class="text-wrapper">목</div>
          <div class="text-wrapper">금</div>
          <div class="text-wrapper">토</div>
        </div>
        <div class="frame-8">
          <div class="frame-9">
            <div class="div-wrapper">
              <div class="element">1</div>
            </div>
            <div class="frame-10">
              <div class="element-2">2</div>
              <div class="element-wrapper">
                <div class="element-3">모의고사</div>
              </div>
            </div>
            <div class="frame-10">
              <div class="element-2">3</div>
            </div>
            <div class="frame-11">
              <div class="element">4</div>
            </div>
            <div class="frame-10">
              <div class="element-2">5</div>
              <div class="element-wrapper">
                <div class="element-3">중간고사</div>
              </div>
            </div>
            <div class="frame-11">
              <div class="element">6</div>
            </div>
            <div class="frame-11">
              <div class="element">7</div>
            </div>
          </div>
          <div class="frame-12">
            <div class="div-wrapper">
              <div class="element-4">8</div>
            </div>
            <div class="frame-11">
              <div class="element-4">9</div>
            </div>
            <div class="group">
              <div class="oval"></div>
              <div class="frame-13">
                <div class="element-5">10</div>
              </div>
            </div>
            <div class="frame-11">
              <div class="element-4">11</div>
            </div>
            <div class="frame-11">
              <div class="element-4">12</div>
            </div>
            <div class="frame-11">
              <div class="element-4">13</div>
            </div>
            <div class="frame-11">
              <div class="element-4">14</div>
            </div>
          </div>
          <div class="frame-14">
            <div class="div-wrapper">
              <div class="frame-15">
                <div class="element">15</div>
              </div>
            </div>
            <div class="frame-16">
              <div class="frame-15">
                <div class="element">16</div>
              </div>
            </div>
            <div class="frame-17">
              <div class="frame-18">
                <div class="element">17</div>
              </div>
            </div>
            <div class="frame-17">
              <div class="frame-18">
                <div class="element">18</div>
              </div>
            </div>
            <div class="frame-16">
              <div class="frame-15">
                <div class="element">19</div>
              </div>
            </div>
            <div class="frame-17">
              <div class="frame-18">
                <div class="element">20</div>
              </div>
            </div>
            <div class="frame-17">
              <div class="frame-18">
                <div class="element">21</div>
              </div>
            </div>
          </div>
          <div class="frame-14">
            <div class="div-wrapper">
              <div class="element">22</div>
            </div>
            <div class="frame-16">
              <div class="element">23</div>
            </div>
            <div class="frame-16">
              <div class="element">24</div>
            </div>
            <div class="frame-16">
              <div class="element">26</div>
            </div>
            <div class="frame-16">
              <div class="element">25</div>
            </div>
            <div class="frame-16">
              <div class="element">27</div>
            </div>
            <div class="frame-16">
              <div class="element">28</div>
            </div>
          </div>
          <div class="frame-14">
            <div class="div-wrapper">
              <div class="element-6">29</div>
            </div>
            <div class="frame-11">
              <div class="element-6">30</div>
            </div>
          </div>
        </div>
      </div> -->

    </div>
  </div>
  <!-- <div class="frame-19">
    <div class="frame-20">
      <div class="frame-21">
        <img class="img" src="img/attendance-2.svg" />
        <div class="text-wrapper-2">출결</div>
      </div>
      <div class="frame-22">
        <img class="img" src="img/calendar-2.svg" />
        <div class="text-wrapper-2">스터디카페 예약</div>
      </div>
    </div>
    <div class="frame-20">
      <div class="frame-23">
        <img class="img" src="img/counsel-3.svg" />
        <div class="text-wrapper-2">학과상담 신청</div>
      </div>
      <div class="frame-24">
        <img class="img" src="img/mentor-4.svg" />
        <div class="text-wrapper-2">멘토 상담</div>
      </div>
    </div>
  </div>
  <div class="app-bar-wrapper">
    <div class="group-wrapper">
      <div class="group-2">
        <div class="frame-25">
          <div class="frame-26">
            <img class="img" src="img/home-pressed-2.svg" />
            <div class="text-wrapper-3">홈</div>
          </div>
          <div class="frame-26">
            <img class="img" src="img/book-2.svg" />
            <div class="text-wrapper-4">학습&amp;출결</div>
          </div>
          <div class="frame-26">
            <img class="img" src="img/message-2.svg" />
            <div class="text-wrapper-4">상담&amp;예약</div>
          </div>
          <div class="frame-26">
            <div class="img">
              <div class="group-3">
                <div class="rectangle"></div>
                <img class="line" src="img/line-1.svg" />
                <img class="line-2" src="img/line-2.svg" />
                <img class="line-3" src="img/line-3.svg" />
              </div>
            </div>
            <div class="text-wrapper-4">학사일정&amp;공지</div>
          </div>
        </div>
      </div>
    </div>
  </div> -->
</div>


<script>
window.addEventListener('load', function () {
  if (!window.FullCalendar) {
    console.error('FullCalendar not loaded');
    return;
  }
  const el = document.getElementById('cal');
  const cal = new FullCalendar.Calendar(el, {
    initialView:'dayGridMonth',
    locale:'ko',
    height:'auto',
    firstDay:0,
    headerToolbar:{left:'prev',center:'title',right:'next'},
    dayMaxEventRows:2,
    events: function(info, success, failure){
      const ym = info.startStr.slice(0,7);
      fetch('/api/ctrl_schedule.php?type=AJAX_CAL_LIST&ym='+ym)
        .then(r=>r.json())
        .then(json=> json.result==='SUCCESS' ? success(json.data) : failure())
        .catch(failure);
    },
    eventContent: (arg) => ({ html: `<span class="gold-tag">${arg.event.title}</span>` }),
    dateClick: function(arg){
      document.querySelectorAll('.fc-daygrid-day').forEach(d=>d.classList.remove('fc-day-selected'));
      arg.dayEl.classList.add('fc-day-selected');
    }
  });
  cal.render();
});
</script>


<?php
include_once(G5_THEME_MOBILE_PATH . '/tail.php');
