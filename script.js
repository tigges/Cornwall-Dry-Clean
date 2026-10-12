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
  const status = document.querySelector("[data-open-status]");
  if (!status) return;

  const now = new Date();
  const day = now.getDay();
  const currentHour = now.getHours() + now.getMinutes() / 60;
  const today = hours[day];

  if (today && currentHour >= today.open && currentHour < today.close) {
    status.textContent = `Open now · closes ${formatHour(today.close)}`;
    return;
  }

  if (today && currentHour < today.open) {
    status.textContent = `Closed · opens today at ${formatHour(today.open)}`;
    return;
  }

  status.textContent = `Closed · opens ${nextOpen(day)}`;
}

function highlightToday() {
  const today = String(new Date().getDay());
  document.querySelectorAll(".hours [data-day]").forEach((row) => {
    row.classList.toggle("is-today", row.dataset.day === today);
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

document.querySelector("[data-year]").textContent = String(new Date().getFullYear());
updateOpenStatus();
highlightToday();
setupNav();
