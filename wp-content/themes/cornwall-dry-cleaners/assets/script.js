// Hours, bank holidays (GOV.UK, England & Wales), special days and the notice
// come from Shop details as window.cornwallShop. Without it the chip keeps the
// static weekly hours printed by the page.
const shop = window.cornwallShop || null;
const hours = shop ? shop.hours : {};
const bankHolidays = new Set(shop ? shop.bankHolidays : []);
const special = shop && shop.special ? shop.special : {};
const notice = shop && shop.notice ? String(shop.notice).trim() : "";
const noticeFrom = shop && shop.noticeFrom ? shop.noticeFrom : "";
const noticeTo = shop && shop.noticeTo ? shop.noticeTo : "";

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

function noticeShowing(ymd) {
  return Boolean(notice) && (!noticeFrom || ymd >= noticeFrom) && (!noticeTo || ymd <= noticeTo);
}

function spanFor(info) {
  if (Object.prototype.hasOwnProperty.call(special, info.ymd)) return special[info.ymd].hours;
  if (bankHolidays.has(info.ymd)) return null;
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
  } else if (special[now.ymd] && !special[now.ymd].hours) {
    const name = special[now.ymd].name;
    if (name) text = `Closed · ${name} (opens ${opens})`;
    else if (noticeShowing(now.ymd) && /^closed\b/i.test(notice)) text = notice;
    else text = `Closed today · opens ${opens}`;
  } else if (bankHolidays.has(now.ymd)) {
    text = `Closed · Bank holiday (opens ${opens})`;
  } else {
    text = `Closed · opens ${opens}`;
  }

  document.querySelectorAll("[data-open-status]").forEach((node) => {
    node.textContent = text;
    node.dataset.state = text.startsWith("Open") ? "open" : "closed";
  });

  if (notice && !noticeShowing(now.ymd)) {
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

// Plausible custom events (goals: Call, Directions, Map, Write a review, Email).
// The tag only loads on the live site, so this is a no-op everywhere else.
function trackArea(link) {
  if (link.closest(".site-notice")) return "notice";
  if (link.closest(".mobile-cta")) return "sticky bar";
  if (link.closest(".mobile-nav")) return "menu";
  if (link.closest(".cornwall-header")) return "header";
  if (link.closest(".cornwall-footer, footer")) return "footer";
  if (link.closest(".b-hero")) return "hero";
  const section = link.closest("section[id]");
  return section ? section.id : "page";
}

function trackName(link) {
  const href = link.getAttribute("href") || "";
  if (href.startsWith("tel:")) return "Call";
  if (href.startsWith("mailto:")) return "Email";
  if (href.includes("google.com/maps/dir")) return "Directions";
  if (link.classList.contains("map-link")) return "Map";
  if (link.hasAttribute("data-review-link") || /\/review(\/|$)/.test(href)) return "Write a review";
  return "";
}

document.addEventListener("click", (event) => {
  const link = event.target.closest("a[href]");
  if (!link || typeof window.plausible !== "function") return;
  const name = trackName(link);
  if (name) window.plausible(name, { props: { where: trackArea(link), page: location.pathname } });
});

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
