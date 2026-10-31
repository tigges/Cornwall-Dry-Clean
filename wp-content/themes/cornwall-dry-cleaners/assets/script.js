// Hours, bank holidays (GOV.UK, England & Wales) and closure dates come from
// functions.php as window.cornwallShop. Without it the chip keeps the static
// weekly hours printed by the page.
const shop = window.cornwallShop || null;
const hours = shop ? shop.hours : {};
const bankHolidays = new Set(shop ? shop.bankHolidays : []);
const closed = shop && shop.closed ? shop.closed : null;
const notice = shop && shop.notice ? String(shop.notice).trim() : "";

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
  const whole = Math.floor(hour);
  const minutes = Math.round((hour - whole) * 60);
  const twelve = whole % 12 || 12;
  return minutes ? `${twelve}:${String(minutes).padStart(2, "0")}${suffix}` : `${twelve}${suffix}`;
}

function isClosedRange(ymd) {
  return Boolean(closed && ymd >= closed.from && ymd <= closed.to);
}

function spanFor(info) {
  if (bankHolidays.has(info.ymd) || isClosedRange(info.ymd)) return null;
  return hours[info.day] || null;
}

function nextOpen(baseDate = new Date()) {
  for (let offset = 1; offset <= 60; offset += 1) {
    const info = londonDateParts(new Date(baseDate.getTime() + offset * 86400000));
    const span = spanFor(info);
    if (span) return `${dayNames[info.day]} ${formatHour(span[0])}`;
  }
  return "";
}

function updateOpenStatus() {
  if (!shop) return;
  const nowDate = new Date();
  const now = londonDateParts(nowDate);
  const today = spanFor(now);
  const opens = nextOpen(nowDate);
  let text;

  if (today && now.hour >= today[0] && now.hour < today[1]) {
    text = `Open now · closes ${formatHour(today[1])}`;
  } else if (today && now.hour < today[0]) {
    text = `Closed · opens today at ${formatHour(today[0])}`;
  } else if (isClosedRange(now.ymd)) {
    if (!notice) text = `Closed today · opens ${opens}`;
    else text = /^closed\b/i.test(notice) ? notice : `Closed · ${notice}`;
  } else if (bankHolidays.has(now.ymd)) {
    text = `Closed · Bank holiday (opens ${opens})`;
  } else {
    text = `Closed · opens ${opens}`;
  }

  document.querySelectorAll("[data-open-status]").forEach((node) => {
    node.textContent = text;
    node.dataset.state = text.startsWith("Open") ? "open" : "closed";
  });

  if (closed && now.ymd > closed.to) {
    document.querySelectorAll("[data-holiday-banner]").forEach((node) => node.remove());
  }
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
