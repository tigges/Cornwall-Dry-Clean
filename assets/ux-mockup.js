function openStatusText() {
  const now = londonNow();
  const today = hours[now.day];
  if (today && now.hour >= today.open && now.hour < today.close) {
    return `Open now · closes ${formatHour(today.close)}`;
  }
  if (today && now.hour < today.open) {
    return `Closed · opens today at ${formatHour(today.open)}`;
  }
  return `Closed · opens ${nextOpen(now.day)}`;
}

function syncOpenStatus() {
  const text = openStatusText();
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
      button.classList.add("copied");
      window.setTimeout(() => {
        button.textContent = "Copy address";
        button.classList.remove("copied");
      }, 2000);
    } catch {
      window.prompt("Copy address", address);
    }
  });
}

function setupConsent() {
  const bar = document.querySelector("[data-consent]");
  const slot = document.querySelector("[data-map-slot]");
  const accepts = document.querySelectorAll("[data-consent-accept]");
  if (!bar || !slot || !accepts.length) return;

  const src =
    "https://maps.google.com/maps?q=Cornwall+Dry+Cleaners,+23+Haven+Lane,+Ealing+W5+2HZ&z=16&output=embed";

  const showMap = () => {
    slot.innerHTML =
      '<iframe class="map map-compact" title="Map showing Cornwall Dry Cleaners on Haven Lane, Ealing" src="' +
      src +
      '" loading="lazy"></iframe>';
    bar.hidden = true;
  };

  accepts.forEach((button) => button.addEventListener("click", showMap));
}

syncOpenStatus();
setupCopyAddress();
setupConsent();
