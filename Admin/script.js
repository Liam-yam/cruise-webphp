"use strict";

(function () {
  const el = document.getElementById("live-date");
  if (!el) return;

  function render() {
    const now = new Date();
    el.innerHTML =
      now.toLocaleDateString("en-PH", { weekday: "long", year: "numeric", month: "long", day: "numeric" }) +
      "<br>" +
      now.toLocaleTimeString("en-PH", { hour: "2-digit", minute: "2-digit" });
  }

  render();
  setInterval(render, 30000);
})();

function openModal(id) {
  document.getElementById(id)?.classList.add("is-open");
}

function closeModal(id) {
  document.getElementById(id)?.classList.remove("is-open");
}

document.querySelectorAll(".modal-backdrop").forEach(backdrop => {
  backdrop.addEventListener("click", event => {
    if (event.target === backdrop) closeModal(backdrop.id);
  });
});

document.querySelectorAll("[data-close]").forEach(button => {
  button.addEventListener("click", () => closeModal(button.dataset.close));
});

document.addEventListener("keydown", event => {
  if (event.key === "Escape") {
    document.querySelectorAll(".modal-backdrop.is-open").forEach(modal => modal.classList.remove("is-open"));
  }
});

document.getElementById("openAddScheduleBtn")?.addEventListener("click", () => {
  document.getElementById("schedule-action").value = "add_schedule";
  document.getElementById("schedule-title").textContent = "Add Ship Calendar";
  ["schedule-ticket-no", "schedule-ship", "schedule-itinerary", "schedule-arrival", "schedule-departure", "schedule-room"].forEach(id => {
    document.getElementById(id).value = "";
  });
  document.getElementById("schedule-status").value = "active";
  openModal("scheduleModal");
});

document.getElementById("openAddTierBtn")?.addEventListener("click", () => {
  document.getElementById("tier-action").value = "add_tier";
  document.getElementById("tier-title").textContent = "Add Tier Price";
  ["tier-id", "tier-name", "tier-base-price", "tier-promo-price"].forEach(id => {
    document.getElementById(id).value = "";
  });
  document.getElementById("tier-status").value = "active";
  openModal("tierModal");
});

document.getElementById("scheduleTable")?.addEventListener("click", event => {
  const editButton = event.target.closest(".edit-schedule-btn");
  const ArchiveButton = event.target.closest(".Archive-schedule-btn");

  if (editButton) {
    const data = editButton.closest("tr").dataset;
    document.getElementById("schedule-action").value = "update_schedule";
    document.getElementById("schedule-title").textContent = "Edit Ship Calendar";
    document.getElementById("schedule-ticket-no").value = data.ticketNo || "";
    document.getElementById("schedule-ship").value = data.ship || "";
    document.getElementById("schedule-itinerary").value = data.itinerary || "";
    document.getElementById("schedule-arrival").value = data.arrival || "";
    document.getElementById("schedule-departure").value = data.departure || "";
    document.getElementById("schedule-room").value = data.room || "";
    document.getElementById("schedule-status").value = data.status || "active";
    openModal("scheduleModal");
  }

  if (ArchiveButton) {
    const data = ArchiveButton.closest("tr").dataset;
    document.getElementById("Archive-schedule-id").value = data.ticketNo || "";
    document.getElementById("Archive-schedule-name").textContent = `${data.ship || "Ship"} (${data.departure || "date"})`;
    openModal("ArchiveScheduleModal");
  }
});

document.getElementById("tierTable")?.addEventListener("click", event => {
  const editButton = event.target.closest(".edit-tier-btn");
  const ArchiveButton = event.target.closest(".Archive-tier-btn");

  if (editButton) {
    const data = editButton.closest("tr").dataset;
    document.getElementById("tier-action").value = "update_tier";
    document.getElementById("tier-title").textContent = "Edit Tier Price";
    document.getElementById("tier-id").value = data.tierId || "";
    document.getElementById("tier-name").value = data.tierName || "";
    document.getElementById("tier-base-price").value = data.basePrice || "";
    document.getElementById("tier-promo-price").value = data.promoPrice || "";
    document.getElementById("tier-status").value = data.status || "active";
    openModal("tierModal");
  }

  if (ArchiveButton) {
    const data = ArchiveButton.closest("tr").dataset;
    document.getElementById("Archive-tier-id").value = data.tierId || "";
    document.getElementById("Archive-tier-name").textContent = data.tierName || "Tier";
    openModal("ArchiveTierModal");
  }
});
