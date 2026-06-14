document.addEventListener("DOMContentLoaded", () => {
  const heroImage = document.querySelector(".hero-image");
  const heroTitle = document.querySelector(".hero-title");
  const bookingCard = document.querySelector(".booking-card");
  const packages = document.querySelectorAll(".package");

  if (heroImage && heroTitle && bookingCard) {
    heroImage.classList.add("show");
    heroTitle.classList.add("show");
    bookingCard.classList.add("show");
  }

  if ("IntersectionObserver" in window) {
    const observer = new IntersectionObserver(
      entries => {
        entries.forEach(entry => {
          if (entry.isIntersecting) {
            entry.target.classList.add("show");
          }
        });
      },
      { threshold: 0.2 }
    );

    packages.forEach(packageCard => observer.observe(packageCard));
  } else {
    packages.forEach(packageCard => packageCard.classList.add("show"));
  }

  const form = document.querySelector(".booking-card");
  const cruiseShip = document.getElementById("cruiseShip");
  const tripDate = document.getElementById("tripDate");
  const dateCard = document.getElementById("dateCard");
  const dateDisplay = document.getElementById("dateDisplay");
  const calendarPopup = document.getElementById("calendarPopup");
  const calMonthYear = document.getElementById("calMonthYear");
  const calGrid = document.getElementById("calGrid");
  const prevMonth = document.getElementById("prevMonth");
  const nextMonth = document.getElementById("nextMonth");
  const tierSelect = document.getElementById("tierSelect");
  const adultCount = document.getElementById("adultCount");
  const childCount = document.getElementById("childCount");
  const guestButtons = document.querySelectorAll(".guest-btn");
  const totalPrice = document.getElementById("totalPrice");
  const totalPriceInput = document.getElementById("totalPriceInput");

  const monthNames = [
    "January",
    "February",
    "March",
    "April",
    "May",
    "June",
    "July",
    "August",
    "September",
    "October",
    "November",
    "December"
  ];

  const tripDates = {
    Tropical: [
      "June 2 - June 6, 2026",
      "June 16 - June 20, 2026",
      "June 30 - July 4, 2026",
      "July 14 - July 18, 2026"
    ],
    "Lost Cities": [
      "June 12 - June 16, 2026",
      "June 26 - June 30, 2026",
      "July 10 - July 14, 2026",
      "July 24 - July 28, 2026"
    ],
    Masquerade: [
      "June 7 - June 11, 2026",
      "June 23 - June 27, 2026",
      "July 9 - July 13, 2026",
      "July 25 - July 29, 2026"
    ]
  };

  const shipClasses = {
    Tropical: "trip-tropical",
    Masquerade: "trip-masquerade",
    "Lost Cities": "trip-lost-cities"
  };

  const tierPrices = {
    PREMIUM: 32879,
    "ELITE LUX": 37987,
    ROYALTY: 49879
  };

  let calYear = 2026;
  let calMonth = 5;
  let selectedTrip = null;

  function parseTripRange(ship, label) {
    const [startPart, endPartWithYear] = label.split(" - ");
    const [endPart, yearPart] = endPartWithYear.split(", ");
    const year = Number(yearPart);
    const [startMonthName, startDayText] = startPart.split(" ");
    const endPieces = endPart.split(" ");
    const endMonthName = endPieces.length === 2 ? endPieces[0] : startMonthName;
    const endDayText = endPieces.length === 2 ? endPieces[1] : endPieces[0];
    const startMonth = monthNames.indexOf(startMonthName);
    const endMonth = monthNames.indexOf(endMonthName);

    return {
      ship,
      label,
      startDate: new Date(year, startMonth, Number(startDayText)),
      endDate: new Date(year, endMonth, Number(endDayText))
    };
  }

  const trips = Object.entries(tripDates).flatMap(([ship, dates]) =>
    dates.map(date => parseTripRange(ship, date))
  );

  function formatPeso(amount) {
    return `\u20b1${amount.toLocaleString("en-PH")}`;
  }

  function sameDay(first, second) {
    return first.getFullYear() === second.getFullYear() &&
      first.getMonth() === second.getMonth() &&
      first.getDate() === second.getDate();
  }

  function tripsForDay(date) {
    const selectedShip = cruiseShip.value;

    return trips.filter(trip => {
      const matchesShip = !selectedShip || trip.ship === selectedShip;
      return matchesShip && date >= trip.startDate && date <= trip.endDate;
    });
  }

  function renderCalendar() {
    if (!calMonthYear || !calGrid) {
      return;
    }

    calMonthYear.textContent = `${monthNames[calMonth]} ${calYear}`;
    calGrid.innerHTML = "";

    const firstDay = new Date(calYear, calMonth, 1).getDay();
    const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();

    for (let i = 0; i < firstDay; i += 1) {
      const emptyCell = document.createElement("div");
      emptyCell.className = "cal-day empty";
      calGrid.appendChild(emptyCell);
    }

    for (let day = 1; day <= daysInMonth; day += 1) {
      const cellDate = new Date(calYear, calMonth, day);
      const dayTrips = tripsForDay(cellDate);
      const cell = document.createElement("button");
      cell.type = "button";
      cell.className = "cal-day";
      cell.textContent = day;

      if (dayTrips.length > 0) {
        const trip = dayTrips[0];
        cell.classList.add(shipClasses[trip.ship], "clickable");

        if (sameDay(cellDate, trip.startDate)) {
          cell.classList.add("range-start");
        }

        if (sameDay(cellDate, trip.endDate)) {
          cell.classList.add("range-end");
        }

        if (selectedTrip && selectedTrip.label === trip.label) {
          cell.classList.add("selected-trip");
        }

        cell.addEventListener("click", event => {
          event.stopPropagation();
          selectTrip(trip);
        });
      }

      calGrid.appendChild(cell);
    }
  }

  function positionCalendar() {
    const rect = dateCard.getBoundingClientRect();
    const width = 300;
    const estimatedHeight = 340;
    let left = rect.left + rect.width / 2 - width / 2;
    left = Math.max(8, Math.min(left, window.innerWidth - width - 8));

    calendarPopup.style.left = `${left}px`;

    if (rect.top < estimatedHeight + 24) {
      calendarPopup.style.top = `${rect.bottom + 14}px`;
      calendarPopup.style.bottom = "auto";
    } else {
      calendarPopup.style.top = "auto";
      calendarPopup.style.bottom = `${window.innerHeight - rect.top + 14}px`;
    }
  }

  function openCalendar() {
    if (!calendarPopup || !dateCard) {
      return;
    }

    positionCalendar();
    calendarPopup.classList.remove("hidden");
    dateCard.setAttribute("aria-expanded", "true");
    renderCalendar();
  }

  function closeCalendar() {
    if (!calendarPopup || !dateCard) {
      return;
    }

    calendarPopup.classList.add("hidden");
    dateCard.setAttribute("aria-expanded", "false");
  }

  function toggleCalendar(event) {
    event.preventDefault();
    event.stopPropagation();

    if (calendarPopup.classList.contains("hidden")) {
      openCalendar();
    } else {
      closeCalendar();
    }
  }

  function selectTrip(trip) {
    selectedTrip = trip;
    tripDate.value = trip.label;
    dateDisplay.textContent = trip.label;
    dateDisplay.classList.add("selected");

    if (!cruiseShip.value) {
      cruiseShip.value = trip.ship;
    }

    closeCalendar();
    renderCalendar();
    updateTotal();
  }

  function resetSelectedDate() {
    selectedTrip = null;
    tripDate.value = "";
    dateDisplay.textContent = "Select Date";
    dateDisplay.classList.remove("selected");
  }

  function changeMonth(delta) {
    calMonth += delta;

    if (calMonth > 11) {
      calMonth = 0;
      calYear += 1;
    }

    if (calMonth < 0) {
      calMonth = 11;
      calYear -= 1;
    }

    renderCalendar();
  }

  function updateTotal() {
    const tier = tierSelect.value;
    const adults = Number(adultCount.value);
    const children = Number(childCount.value);

    if (!tier) {
      totalPrice.textContent = "\u20b10";
      totalPriceInput.value = "0";
      return;
    }

    const adultPrice = tierPrices[tier];
    const childPrice = adultPrice * 0.5;
    const total = adults * adultPrice + children * childPrice;

    totalPrice.textContent = formatPeso(total);
    totalPriceInput.value = total;
  }

  if (dateCard && calendarPopup) {
    dateCard.addEventListener("click", toggleCalendar);
    dateCard.addEventListener("keydown", event => {
      if (event.key === "Enter" || event.key === " ") {
        toggleCalendar(event);
      }
    });

    calendarPopup.addEventListener("click", event => event.stopPropagation());
    document.addEventListener("click", closeCalendar);
    window.addEventListener("resize", () => {
      if (!calendarPopup.classList.contains("hidden")) {
        positionCalendar();
      }
    });
  }

  if (prevMonth && nextMonth) {
    prevMonth.addEventListener("click", event => {
      event.stopPropagation();
      changeMonth(-1);
    });

    nextMonth.addEventListener("click", event => {
      event.stopPropagation();
      changeMonth(1);
    });
  }

  if (cruiseShip && tierSelect) {
    cruiseShip.addEventListener("change", () => {
      resetSelectedDate();
      renderCalendar();
      updateTotal();
    });

    tierSelect.addEventListener("change", updateTotal);
  }

  guestButtons.forEach(button => {
    button.addEventListener("click", () => {
      const type = button.dataset.type;
      const action = button.dataset.action;
      const input = type === "adult" ? adultCount : childCount;
      const min = Number(input.min);
      let value = Number(input.value);

      if (action === "plus") {
        value += 1;
      }

      if (action === "minus" && value > min) {
        value -= 1;
      }

      input.value = value;
      updateTotal();
    });
  });

  if (form) {
    form.addEventListener("submit", event => {
      if (!tripDate.value) {
        event.preventDefault();
        openCalendar();
        alert("Please choose a trip date.");
      }
    });
  }

  renderCalendar();
  updateTotal();

  const paymentModal = document.getElementById("payBackdrop");

  if (paymentModal) {
    document.body.style.overflow = "hidden";
    paymentModal.addEventListener("click", event => {
      if (event.target === paymentModal) {
        paymentModal.classList.remove("visible");
        document.body.style.overflow = "";
      }
    });
  }

  const paymentMethodSelect = document.getElementById("payMethodSelect");
  const paymentFieldGroups = document.querySelectorAll("[data-payment-fields]");

  function updatePaymentFields() {
    const selectedMethod = paymentMethodSelect ? paymentMethodSelect.value : "";

    paymentFieldGroups.forEach(group => {
      const methods = group.dataset.paymentFields.split(" ");
      const isActive = methods.includes(selectedMethod);
      const inputs = group.querySelectorAll("input");

      group.classList.toggle("hidden", !isActive);

      inputs.forEach(input => {
        input.disabled = !isActive;
        input.required = isActive;

        if (!isActive) {
          input.value = "";
        }
      });
    });
  }

  if (paymentMethodSelect) {
    paymentMethodSelect.addEventListener("change", updatePaymentFields);
    updatePaymentFields();
  }
  // --- GCash Number validation (11 digits, starts with 09) ---
  const gcashNumberInput = document.getElementById("gcashNumberInput");
  const gcashNumberError = document.getElementById("gcashNumberError");

  function validateGcashNumber() {
    if (!gcashNumberInput || !gcashNumberError) return true;
    const val = gcashNumberInput.value;
    const isValid = /^09[0-9]{9}$/.test(val);
    gcashNumberError.style.display = isValid ? "none" : "block";
    gcashNumberInput.classList.toggle("input-error", !isValid);
    return isValid;
  }

  if (gcashNumberInput) {
    gcashNumberInput.addEventListener("input", () => {
      let v = gcashNumberInput.value.replace(/\D/g, "");
      // Auto-prepend 09 if user starts typing a digit other than 0, or just 0
      if (v.length > 0 && v[0] !== "0") v = "0" + v;
      if (v.length > 1 && v[1] !== "9") v = v[0] + "9" + v.substring(2);
      gcashNumberInput.value = v.slice(0, 11);
      validateGcashNumber();
    });
    gcashNumberInput.addEventListener("blur", () => {
      if (gcashNumberInput.disabled === false) validateGcashNumber();
    });
  }

  // --- Maya Number validation (11 digits, starts with 09) ---
  const mayaNumberInput = document.getElementById("mayaNumberInput");
  const mayaNumberError = document.getElementById("mayaNumberError");

  function validateMayaNumber() {
    if (!mayaNumberInput || !mayaNumberError) return true;
    const val = mayaNumberInput.value;
    const isValid = /^09[0-9]{9}$/.test(val);
    mayaNumberError.style.display = isValid ? "none" : "block";
    mayaNumberInput.classList.toggle("input-error", !isValid);
    return isValid;
  }

  if (mayaNumberInput) {
    mayaNumberInput.addEventListener("input", () => {
      let v = mayaNumberInput.value.replace(/\D/g, "");
      if (v.length > 0 && v[0] !== "0") v = "0" + v;
      if (v.length > 1 && v[1] !== "9") v = v[0] + "9" + v.substring(2);
      mayaNumberInput.value = v.slice(0, 11);
      validateMayaNumber();
    });
    mayaNumberInput.addEventListener("blur", () => {
      if (mayaNumberInput.disabled === false) validateMayaNumber();
    });
  }

  // Re-validate when method changes
  if (paymentMethodSelect) {
    paymentMethodSelect.addEventListener("change", () => {
      if (paymentMethodSelect.value === "GCash") setTimeout(validateGcashNumber, 50);
      if (paymentMethodSelect.value === "Maya") setTimeout(validateMayaNumber, 50);
    });
  }
  // --- Maya Reference Number validation (12 alphanumeric) ---
  const mayaRefInput = document.getElementById("mayaReferenceInput");
  const mayaRefError = document.getElementById("mayaRefError");

  function validateMayaRef() {
    if (!mayaRefInput || !mayaRefError) return true;
    const val = mayaRefInput.value;
    const isValid = /^[A-Za-z0-9]{12}$/.test(val);
    mayaRefError.style.display = isValid ? "none" : "block";
    mayaRefInput.classList.toggle("input-error", !isValid);
    return isValid;
  }

  if (mayaRefInput) {
    mayaRefInput.addEventListener("input", () => {
      mayaRefInput.value = mayaRefInput.value.replace(/[^A-Za-z0-9]/g, "").slice(0, 12).toUpperCase();
      validateMayaRef();
    });
    mayaRefInput.addEventListener("blur", () => {
      if (mayaRefInput.disabled === false) validateMayaRef();
    });
    if (paymentMethodSelect) {
      paymentMethodSelect.addEventListener("change", () => {
        if (paymentMethodSelect.value === "Maya") {
          setTimeout(validateMayaRef, 50);
        }
      });
    }
  }

  // --- BPI Card validation (matches Mastercard format) ---
  const bpiCardNumber = document.querySelector('input[name="bpi_card_number"]');
  const bpiCardExpiry = document.querySelector('input[name="bpi_card_expiry"]');
  const bpiCardCvv = document.querySelector('input[name="bpi_card_cvv"]');

  function validateBpiCard() {
    let ok = true;
    if (bpiCardNumber && !bpiCardNumber.disabled) {
      const digits = bpiCardNumber.value.replace(/\D/g, "");
      const valid = digits.length >= 13 && digits.length <= 19;
      bpiCardNumber.classList.toggle("input-error", !valid);
      if (!valid) ok = false;
    }
    if (bpiCardExpiry && !bpiCardExpiry.disabled) {
      const valid = /^(0[1-9]|1[0-2])\/[0-9]{2}$/.test(bpiCardExpiry.value);
      bpiCardExpiry.classList.toggle("input-error", !valid);
      if (!valid) ok = false;
    }
    if (bpiCardCvv && !bpiCardCvv.disabled) {
      const valid = /^[0-9]{3,4}$/.test(bpiCardCvv.value);
      bpiCardCvv.classList.toggle("input-error", !valid);
      if (!valid) ok = false;
    }
    return ok;
  }

  if (bpiCardNumber) {
    bpiCardNumber.addEventListener("input", () => {
      bpiCardNumber.value = bpiCardNumber.value.replace(/\D/g, "").slice(0, 19);
      validateBpiCard();
    });
    bpiCardExpiry.addEventListener("input", validateBpiCard);
    bpiCardCvv.addEventListener("input", () => {
      bpiCardCvv.value = bpiCardCvv.value.replace(/\D/g, "").slice(0, 4);
      validateBpiCard();
    });
  }

  // Block form submit for Maya / BPI invalid input
  if (form) {
    form.addEventListener("submit", event => {
      const m = paymentMethodSelect ? paymentMethodSelect.value : "";
      if (m === "Maya" && !validateMayaRef()) {
        event.preventDefault();
        alert("Invalid Maya reference number. It must be exactly 12 alphanumeric characters.");
        if (mayaRefInput) mayaRefInput.focus();
      } else if (m === "BPI" && !validateBpiCard()) {
        event.preventDefault();
        alert("Please complete all BPI card fields correctly (card number, expiry MM/YY, CVV).");
        if (bpiCardNumber) bpiCardNumber.focus();
      }
    });
  }
  // --- GCash Reference Number validation (must be after paymentMethodSelect is defined) ---
  const gcashRefInput = document.getElementById("gcashReferenceInput");
  const gcashRefError = document.getElementById("gcashRefError");

  function validateGcashRef() {
    if (!gcashRefInput || !gcashRefError) return true;
    const val = gcashRefInput.value;
    const isValid = /^[0-9]{13}$/.test(val);
    gcashRefError.style.display = isValid ? "none" : "block";
    gcashRefInput.classList.toggle("input-error", !isValid);
    return isValid;
  }

  if (gcashRefInput) {
    // Live: strip non-digits as the user types
    gcashRefInput.addEventListener("input", () => {
      gcashRefInput.value = gcashRefInput.value.replace(/\D/g, "").slice(0, 13);
      validateGcashRef();
    });

    // Blur: show error immediately when leaving the field
    gcashRefInput.addEventListener("blur", () => {
      if (gcashRefInput.disabled === false) validateGcashRef();
    });

    // Re-validate when GCash is selected as the payment method
    if (paymentMethodSelect) {
      paymentMethodSelect.addEventListener("change", () => {
        if (paymentMethodSelect.value === "GCash") {
          setTimeout(validateGcashRef, 50);
        }
      });
    }
  }

  // Block form submit if GCash reference is invalid
  if (form) {
    form.addEventListener("submit", event => {
      if (paymentMethodSelect && paymentMethodSelect.value === "GCash") {
        if (!validateGcashRef()) {
          event.preventDefault();
          alert("Invalid GCash reference number. It must be exactly 13 digits.");
          if (gcashRefInput) gcashRefInput.focus();
        }
      }
    });
  }

  const ticketBackdrop = document.getElementById("ticketBackdrop");
  const paidTicket = document.getElementById("paidTicket");
  const ticketClose = ticketBackdrop ? ticketBackdrop.querySelector("[data-ticket-close]") : null;
  const ticketDownload = document.getElementById("ticketDownload");

  if (ticketBackdrop && paidTicket) {
    document.body.style.overflow = "hidden";

    if (ticketClose) {
      window.setTimeout(() => {
        ticketClose.disabled = false;
        ticketClose.removeAttribute("title");
      }, 4000);

      ticketClose.title = "Available after 4 seconds";
      ticketClose.addEventListener("click", () => {
        ticketBackdrop.classList.remove("visible");
        document.body.style.overflow = "";
      });
    }
  }

  function loadTicketImage(src) {
    return new Promise(resolve => {
      const image = new Image();
      image.onload = () => resolve(image);
      image.onerror = () => resolve(null);
      image.src = src;
    });
  }

  function drawCoverImage(context, image, x, y, width, height) {
    const scale = Math.max(width / image.width, height / image.height);
    const drawWidth = image.width * scale;
    const drawHeight = image.height * scale;
    const drawX = x + (width - drawWidth) / 2;
    const drawY = y + (height - drawHeight) / 2;

    context.drawImage(image, drawX, drawY, drawWidth, drawHeight);
  }

  function roundedRect(context, x, y, width, height, radius) {
    if (typeof context.roundRect === "function") {
      context.roundRect(x, y, width, height, radius);
      return;
    }

    context.moveTo(x + radius, y);
    context.lineTo(x + width - radius, y);
    context.quadraticCurveTo(x + width, y, x + width, y + radius);
    context.lineTo(x + width, y + height - radius);
    context.quadraticCurveTo(x + width, y + height, x + width - radius, y + height);
    context.lineTo(x + radius, y + height);
    context.quadraticCurveTo(x, y + height, x, y + height - radius);
    context.lineTo(x, y + radius);
    context.quadraticCurveTo(x, y, x + radius, y);
  }

  function drawTextField(context, label, value, x, y, width) {
    context.fillStyle = "#3b3b3b";
    context.font = "24px Arial";
    context.fillText(label, x, y);

    context.fillStyle = "#d8d8d8";
    context.strokeStyle = "#2b77a8";
    context.lineWidth = 1;
    context.beginPath();
    roundedRect(context, x, y + 8, width, 34, 9);
    context.fill();
    context.stroke();

    context.fillStyle = "#183c5a";
    context.font = "bold 15px Arial";
    context.fillText(value, x + 12, y + 31);
  }

  function drawBarcode(context, x, y, width, height) {
    context.fillStyle = "#111111";

    for (let cursor = x; cursor < x + width; cursor += 7) {
      const barWidth = cursor % 3 === 0 ? 2 : 4;
      context.fillRect(cursor, y, barWidth, height);
    }
  }

  function fitText(context, text, x, y, maxWidth, startSize, fontFamily) {
    let size = startSize;

    do {
      context.font = `bold italic ${size}px ${fontFamily}`;
      size -= 1;
    } while (context.measureText(text).width > maxWidth && size > 16);

    context.fillText(text, x, y);
  }

  async function drawSingleTicket(data, seatSuffix, guestLabel) {
    const canvas = document.createElement("canvas");
    canvas.width = 1203;
    canvas.height = 487;

    const context = canvas.getContext("2d");
    const image = await loadTicketImage(data.ticketImage);
    const logo = await loadTicketImage(data.ticketLogo);

    context.fillStyle = "#ffffff";
    context.fillRect(0, 0, canvas.width, canvas.height);

    context.fillStyle = "#2b5d7c";
    context.fillRect(0, 0, 117, 487);

    context.save();
    context.translate(58, 244);
    context.rotate(-Math.PI / 2);
    context.fillStyle = "#ffffff";
    context.font = "bold italic 48px Georgia";
    context.textAlign = "center";
    context.fillText("BOARDING PASS", 0, 16);
    context.restore();

    if (image) {
      drawCoverImage(context, image, 117, 0, 641, 487);
      context.fillStyle = "rgba(9, 29, 54, 0.28)";
      context.fillRect(117, 0, 641, 487);
    } else {
      context.fillStyle = "#1a3a5c";
      context.fillRect(117, 0, 641, 487);
    }

    context.fillStyle = "#ffffff";
    context.font = "bold italic 28px Georgia";
    context.textAlign = "left";
    context.fillText(data.ticketTitle, 138, 48);

    context.fillStyle = "#ffffff";
    context.fillRect(758, 0, 445, 487);

    if (logo) {
      context.save();
      context.globalAlpha = 0.09;
      context.drawImage(logo, 892, 100, 250, 250);
      context.restore();
    }

    context.fillStyle = "#2b5d7c";
    fitText(context, data.ticketShip, 790, 58, 178, 28, "Georgia");
    drawBarcode(context, 986, 16, 210, 60);

    drawTextField(context, "Name", data.ticketName, 780, 132, 370);
    drawTextField(context, "Ticket Type", data.ticketType, 780, 202, 210);
    drawTextField(context, "From", data.ticketFrom, 780, 282, 160);
    drawTextField(context, "To", data.ticketTo, 990, 282, 150);

    // Per-guest seat number: append suffix to distinguish seats
    const seatNumber = data.ticketRoom + seatSuffix;
    context.fillStyle = "#3b3b3b";
    context.font = "22px Arial";
    context.fillText("Room Number:", 780, 382);
    context.fillStyle = "#d8d8d8";
    context.strokeStyle = "#2b77a8";
    context.beginPath();
    roundedRect(context, 980, 354, 112, 34, 9);
    context.fill();
    context.stroke();
    context.fillStyle = "#183c5a";
    context.font = "bold 15px Arial";
    context.fillText(seatNumber, 992, 377);

    context.fillStyle = "#151515";
    context.font = "18px Arial";
    context.fillText(`Date and Time Issued: ${data.ticketIssued}`, 780, 414);
    context.fillText(`Departure Date: ${data.ticketDeparture}`, 780, 444);
    context.fillText(`Order Number: ${data.ticketOrder}`, 780, 474);

    // Guest label (e.g. "Guest 2 of 3") drawn in the photo area
    if (guestLabel) {
      context.fillStyle = "rgba(255,255,255,0.85)";
      context.font = "bold 20px Arial";
      context.textAlign = "left";
      context.fillText(guestLabel, 130, 472);
    }

    return canvas;
  }

  async function downloadTicketPng() {
    if (!paidTicket) {
      return;
    }

    const data = paidTicket.dataset;

    // Parse total guest count from data-ticket-guests (e.g. "3 Guests" or "1 Guest")
    const guestCount = parseInt(data.ticketGuests) || 1;
    // Seat suffixes: first ticket keeps the original room, extras get -2, -3, etc.
    const seatSuffixes = ["", ...Array.from({ length: guestCount - 1 }, (_, i) => `-${i + 2}`)];

    for (let i = 0; i < guestCount; i++) {
      const guestLabel = guestCount > 1 ? `Guest ${i + 1} of ${guestCount}` : null;
      const canvas = await drawSingleTicket(data, seatSuffixes[i], guestLabel);
      const baseName = (data.ticketDownload || "paglaot-ticket.png").replace(/\.png$/i, "");
      const fileName = guestCount > 1 ? `${baseName}-guest-${i + 1}.png` : `${baseName}.png`;

      // Small delay between downloads so the browser doesn't block them
      await new Promise((resolve) => setTimeout(resolve, i * 200));

      const link = document.createElement("a");
      link.href = canvas.toDataURL("image/png");
      link.download = fileName;
      link.click();
    }
  }

  if (ticketDownload) {
    ticketDownload.addEventListener("click", downloadTicketPng);
  }
});
