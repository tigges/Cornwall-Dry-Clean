const hours = {
  0: null,
  1: { open: 8, close: 18 },
  2: { open: 8, close: 18 },
  3: { open: 8, close: 18 },
  4: { open: 8, close: 18 },
  5: { open: 8, close: 18 },
  6: { open: 9, close: 17 },
};

const dayNames = ["Sunday", "Monday", "Tuesday", "Wednesday", "Thursday", "Friday", "Saturday"];
const weekdayIndex = { Sun: 0, Mon: 1, Tue: 2, Wed: 3, Thu: 4, Fri: 5, Sat: 6 };

function londonNow() {
  const parts = new Intl.DateTimeFormat("en-GB", {
    timeZone: "Europe/London",
    weekday: "short",
    hour: "numeric",
    minute: "numeric",
    hourCycle: "h23",
  }).formatToParts(new Date());
  const values = Object.fromEntries(parts.filter((part) => part.type !== "literal").map((part) => [part.type, part.value]));
  return {
    day: weekdayIndex[values.weekday] ?? 0,
    hour: Number(values.hour) + Number(values.minute) / 60,
  };
}

function formatHour(hour) {
  const suffix = hour >= 12 ? "pm" : "am";
  const twelve = hour % 12 || 12;
  return `${twelve}${suffix}`;
}

function nextOpen(fromDay) {
  for (let offset = 1; offset <= 7; offset += 1) {
    const day = (fromDay + offset) % 7;
    if (hours[day]) {
      return `${dayNames[day]} ${formatHour(hours[day].open)}`;
    }
  }
  return "Monday 8am";
}

function updateOpenStatus() {
  const now = londonNow();
  const today = hours[now.day];
  let text = `Closed · opens ${nextOpen(now.day)}`;

  if (today && now.hour >= today.open && now.hour < today.close) {
    text = `Open now · closes ${formatHour(today.close)}`;
  } else if (today && now.hour < today.open) {
    text = `Closed · opens today at ${formatHour(today.open)}`;
  }

  document.querySelectorAll("[data-open-status]").forEach((node) => {
    node.textContent = text;
  });
}

function setupCopyAddress() {
  const button = document.querySelector("[data-copy-address]");
  if (!button) return;
  const address = "23 Haven Lane, Ealing, London W5 2HZ";
  button.addEventListener("click", async () => {
    try {
      await navigator.clipboard.writeText(address);
      button.textContent = "Copied";
      window.setTimeout(() => {
        button.textContent = "Copy address";
      }, 2000);
    } catch {
      window.prompt("Copy address", address);
    }
  });
}

function highlightToday() {
  const today = String(londonNow().day);
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
  if (!toggle || !menu) return;

  const close = () => {
    menu.hidden = true;
    toggle.setAttribute("aria-expanded", "false");
  };

  toggle.addEventListener("click", () => {
    const open = menu.hidden;
    menu.hidden = !open;
    toggle.setAttribute("aria-expanded", String(open));
  });

  menu.querySelectorAll("a").forEach((link) => link.addEventListener("click", close));
}

const year = document.querySelector("[data-year]");
if (year) year.textContent = String(new Date().getFullYear());
updateOpenStatus();
highlightToday();
setupNav();
setupCopyAddress();
