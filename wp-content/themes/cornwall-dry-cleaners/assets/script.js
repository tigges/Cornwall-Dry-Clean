const hours = {
  0: null,
  1: { open: 8, close: 18 },
  2: { open: 8, close: 18 },
  3: { open: 8, close: 18 },
  4: { open: 8, close: 18 },
  5: { open: 8, close: 18 },
  6: { open: 9, close: 17 },
};

// England & Wales bank holidays (2026–2028).
const bankHolidays = new Set([
  "2026-01-01", "2026-04-03", "2026-04-06", "2026-05-04", "2026-05-25", "2026-08-31", "2026-12-25", "2026-12-28",
  "2027-01-01", "2027-03-26", "2027-03-29", "2027-05-03", "2027-05-31", "2027-08-30", "2027-12-27", "2027-12-28",
  "2028-01-03", "2028-04-14", "2028-04-17", "2028-05-01", "2028-05-29", "2028-08-28", "2028-12-25", "2028-12-26",
]);

const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
const weekdayIndex = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };

function londonDateParts(date = new Date()) {
  const parts = new Intl.DateTimeFormat("en-GB", {
    timeZone: "Europe/London",
    year: "numeric",
    month: "2-digit",
    day: "2-digit",
    weekday: "short",
    hour: "numeric",
    minute: "numeric",
    hourCycle: "h23",
  }).formatToParts(date);
  const values = Object.fromEntries(parts.filter((part) => part.type !== "literal").map((part) => [part.type, part.value]));
  return {
    ymd: `${values.year}-${values.month}-${values.day}`,
    day: weekdayIndex[values.weekday] ?? 0,
    hour: Number(values.hour) + Number(values.minute) / 60,
  };
}

function formatHour(hour) {
  const suffix = hour >= 12 ? "pm" : "am";
  const twelve = hour % 12 || 12;
  return `${twelve}${suffix}`;
}

function nextOpen(baseDate = new Date()) {
  for (let offset = 1; offset <= 10; offset += 1) {
    const candidate = new Date(baseDate.getTime() + offset * 86400000);
    const info = londonDateParts(candidate);
    if (hours[info.day] && !bankHolidays.has(info.ymd)) {
      return `${dayNames[info.day]} ${formatHour(hours[info.day].open)}`;
    }
  }
  return "Monday 8am";
}

function updateOpenStatus() {
  const nowDate = new Date();
  const now = londonDateParts(nowDate);
  const isBankHoliday = bankHolidays.has(now.ymd);
  const today = isBankHoliday ? null : hours[now.day];
  let text = isBankHoliday
    ? `Closed · Bank holiday (opens ${nextOpen(nowDate)})`
    : `Closed · opens ${nextOpen(nowDate)}`;

  if (today && now.hour >= today.open && now.hour < today.close) {
    text = `Open now · closes ${formatHour(today.close)}`;
  } else if (today && now.hour < today.open) {
    text = `Closed · opens today at ${formatHour(today.open)}`;
  }

  document.querySelectorAll("[data-open-status]").forEach((node) => {
    const holidayClosed = node.dataset.holidayClosed === "1";
    const holidayNotice = (node.dataset.holidayNotice || "").trim();
    let finalText = text;
    if (holidayClosed) {
      finalText = holidayNotice ? `Closed · ${holidayNotice}` : `Closed today · opens ${nextOpen(nowDate)}`;
    }
    node.textContent = finalText;
    node.dataset.state = finalText.startsWith("Open") ? "open" : "closed";
  });
}

function highlightToday() {
  const today = String(londonDateParts().day);
  document.querySelectorAll(".hours [data-day]").forEach((row) => {
    const isToday = row.dataset.day === today;
    row.classList.toggle("is-today", isToday);

    let badge = row.querySelector(".today-badge");
    if (isToday && !badge) {
      badge = document.createElement("span");
      badge.className = "today-badge";
      badge.textContent = "Today";
      row.querySelector("th").append(badge);
    }
    if (!isToday && badge) badge.remove();
  });
}

function setupNav() {
  const toggle = document.querySelector("[data-nav-toggle]");
  const menu = document.querySelector("[data-mobile-nav]");
  const header = document.querySelector(".cornwall-header");
  if (!toggle || !menu) return;

  const close = () => {
    if (menu.hidden) return;
    menu.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
  };

  toggle.addEventListener("click", () => {
    const open = menu.hidden;
    menu.hidden = !open;
    toggle.setAttribute("aria-expanded", String(open));
  });

  menu.querySelectorAll("a").forEach((link) => link.addEventListener("click", close));

  document.addEventListener("keydown", (event) => {
    if (event.key === "Escape") close();
  });

  document.addEventListener("click", (event) => {
    if (!menu.hidden && header && !header.contains(event.target)) {
      close();
    }
  });
}

const year = document.querySelector("[data-year]");
if (year) year.textContent = String(new Date().getFullYear());
document.querySelectorAll("a[data-review-link]").forEach((link) => {
  if (link.dataset.reviewLink) link.href = link.dataset.reviewLink;
});
updateOpenStatus();
highlightToday();
setupNav();
setInterval(() => {
  updateOpenStatus();
  highlightToday();
}, 60000);
