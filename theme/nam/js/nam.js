const currentMonthText = document.getElementById("currentMonth");
const calendarDays = document.getElementById("calendarDays");

let today = new Date();
let currentYear = today.getFullYear();
let currentMonth = today.getMonth();

function renderCalendar() {
  const firstDay = new Date(currentYear, currentMonth, 1);
  const lastDay = new Date(currentYear, currentMonth + 1, 0);
  const startDay = firstDay.getDay();

  currentMonthText.innerText = `${currentYear}년 ${currentMonth + 1}월`;
  calendarDays.innerHTML = "";

  // 앞쪽 빈칸
  for (let i = 0; i < startDay; i++) {
    const empty = document.createElement("div");
    calendarDays.appendChild(empty);
  }

  // 날짜 채우기
  for (let d = 1; d <= lastDay.getDate(); d++) {
    let day = document.createElement("div");
    day.classList.add("calendar-day");
    day.innerText = d;

    // 오늘
    if (
      today.getFullYear() === currentYear &&
      today.getMonth() === currentMonth &&
      today.getDate() === d
    ) {
      day.classList.add("today");
    }

    // 날짜 클릭
    day.addEventListener("click", () => {
      document.querySelectorAll(".calendar-day").forEach(el => {
        el.classList.remove("selected");
      });
      day.classList.add("selected");
    });

    calendarDays.appendChild(day);
  }
}

// 월 이동
document.getElementById("prevMonth").addEventListener("click", () => {
  currentMonth--;
  if (currentMonth < 0) {
    currentMonth = 11;
    currentYear--;
  }
  renderCalendar();
});

document.getElementById("nextMonth").addEventListener("click", () => {
  currentMonth++;
  if (currentMonth > 11) {
    currentMonth = 0;
    currentYear++;
  }
  renderCalendar();
});

// 초기 렌더
renderCalendar();
