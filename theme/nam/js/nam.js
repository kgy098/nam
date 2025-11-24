/* ==========================================================
   NAM Mobile App Main Script (Calendar + Schedule API 연동)
   ========================================================== */
function formatDate(year, month, day) {
  const mm = (month + 1).toString().padStart(2, "0");
  const dd = day.toString().padStart(2, "0");
  return `${year}-${mm}-${dd}`;
}
/* -------------------------
   날짜 상태값
------------------------- */
let today = new Date();
let currentYear = today.getFullYear();
let currentMonth = today.getMonth();

/* -------------------------
   DOM
------------------------- */
const currentMonthText = document.getElementById("currentMonth");
const calendarDays = document.getElementById("calendarDays");

const leftArrow = document.getElementById("prevMonth");
const rightArrow = document.getElementById("nextMonth");

/* -------------------------------------
   일정 데이터 저장 (key: 날짜 → value: 제목)
-------------------------------------- */
let scheduleMap = {};

/* -------------------------
   월별 일정 데이터 로드
------------------------- */
function loadSchedule(year, month) {
  return ScheduleAPI.list(1, 200).then(function (res) {
    scheduleMap = {};
    const list = Array.isArray(res.data) ? res.data : [];

    list.forEach(function (item) {
      const date = new Date(item.start_date);

      if (date.getFullYear() === year && date.getMonth() === month) {
        const day = date.getDate();
        scheduleMap[day] = item.title;
      }
    });
  })
  ;
}

/* -------------------------
   달력 렌더링
------------------------- */
function renderCalendar() {
  const firstDay = new Date(currentYear, currentMonth, 1);
  const lastDay = new Date(currentYear, currentMonth + 1, 0);
  const startDay = firstDay.getDay();

  currentMonthText.innerText = `${currentYear}년 ${currentMonth + 1}월`;

  calendarDays.innerHTML = "";

  /* ---- 앞 빈칸 ---- */
  for (let i = 0; i < startDay; i++) {
    const emptyCell = document.createElement("div");
    emptyCell.classList.add("calendar-day", "empty");
    calendarDays.appendChild(emptyCell);
  }

  /* ---- 날짜 생성 ---- */
  for (let d = 1; d <= lastDay.getDate(); d++) {
    let day = document.createElement("div");
    day.classList.add("calendar-day");

    const dayNumber = document.createElement("div");
    dayNumber.className = "day-number";

    /* -----------------------------
       오늘 날짜 (골드 원형 표시)
    ----------------------------- */
    const isToday =
      today.getFullYear() === currentYear &&
      today.getMonth() === currentMonth &&
      today.getDate() === d;

    if (isToday) {
      const circle = document.createElement("span");
      circle.className = "today-circle";
      circle.textContent = d;

      dayNumber.appendChild(circle);
      day.classList.add("today-cell");
    } else {
      dayNumber.textContent = d;
    }

    day.appendChild(dayNumber);

    /* -----------------------------
       일정 라벨 표시
    ----------------------------- */
    if (scheduleMap[d]) {
      const label = document.createElement("div");
      label.className = "event-label";
      label.innerText = scheduleMap[d];
      day.appendChild(label);
    }

    /* 클릭: selected 표시 */
    day.addEventListener("click", () => {
      document.querySelectorAll(".calendar-day").forEach((el) => {
        el.classList.remove("selected");
      });
      day.classList.add("selected");

      const dateStr = formatDate(currentYear, currentMonth, d);

      // DB에서 일정 가져오기
      ScheduleAPI.list(1, 200).then((res) => {
        const list = res.data;

        const schedules = list.filter((item) => item.start_date === dateStr);

        // 레이어 팝업 띄우기
        openSchedulePopup(dateStr, schedules);
      });
    });

    calendarDays.appendChild(day);
  }
}

/* -------------------------
   월 이동
------------------------- */
leftArrow.addEventListener("click", () => {
  currentMonth--;
  if (currentMonth < 0) {
    currentMonth = 11;
    currentYear--;
  }
  loadSchedule(currentYear, currentMonth).then(renderCalendar);
});

rightArrow.addEventListener("click", () => {
  currentMonth++;
  if (currentMonth > 11) {
    currentMonth = 0;
    currentYear++;
  }
  loadSchedule(currentYear, currentMonth).then(renderCalendar);
});

/* -------------------------
   첫 렌더링
------------------------- */
loadSchedule(currentYear, currentMonth).then(renderCalendar);
