// SLIDER
        let current = 0;
        const total = 5;
        const slides = document.getElementById('slides');
        const dots = document.querySelectorAll('.dot');

        function goTo(index) {
          current = index;
          slides.style.transform = `translateX(-${current * 100}%)`;
          dots.forEach((d, i) => d.classList.toggle('active', i === current));
        }

        let sliderTimer;

        function startTimer() {
          clearInterval(sliderTimer);
          sliderTimer = setInterval(() => goTo((current + 1) % total), 4000);
        }

        // Find the video and pause auto-advance while it plays
        const heroVideo = document.querySelector('.slide-video video');

        heroVideo.addEventListener('play', () => {
          clearInterval(sliderTimer); // stop the timer when video plays
        });

        heroVideo.addEventListener('ended', () => {
          goTo((current + 1) % total); // go to next slide when video finishes
          startTimer(); // resume normal timer
        });

        // Override goTo to pause video if we leave its slide
        const videoSlideIndex = 4; // change this if your video is not the 5th slide

        const originalGoTo = goTo;
        window.goTo = function(index) {
          originalGoTo(index);
          if (index === videoSlideIndex) {
            heroVideo.currentTime = 0;
            heroVideo.play();
            clearInterval(sliderTimer); // don't auto-advance while video plays
          } else {
            heroVideo.pause();
            startTimer();
          }
        }

        startTimer();

      

        // ── BOOKING WIDGET ──
        const adults = {
          count: 1
        };
        const children = {
          count: 0
        };

        const shipData = {
          tropical: {
            label: 'Tropical',
            route: 'Subic → Cebu → Davao → Subic',
            trips: [{
                label: 'Trip 1',
                startDay: 2,
                endDay: 6
              },
              {
                label: 'Trip 2',
                startDay: 16,
                endDay: 20
              }
            ]
          },
          masquerade: {
            label: 'Masquerade',
            route: 'Ozamiz → Iloilo → Elyu → Ozamiz',
            trips: [{
                label: 'Trip 1',
                startDay: 7,
                endDay: 11
              },
              {
                label: 'Trip 2',
                startDay: 23,
                endDay: 27
              }
            ]
          },
          lostcity: {
            label: 'Lost City',
            route: 'Caticlan → Galas → PP → Caticlan',
            trips: [{
                label: 'Trip 1',
                startDay: 12,
                endDay: 16
              },
              {
                label: 'Trip 2',
                startDay: 26,
                endDay: 30
              }
            ]
          }
        };

        const tierPrices = {
          premium: {
            single: 32879,
            promo: 60684
          },
          elite: {
            single: 37987,
            promo: 72584
          },
          ultimate: {
            single: 49879,
            promo: 95684
          }
        };

        const monthNames = ['January', 'February', 'March', 'April', 'May', 'June',
          'July', 'August', 'September', 'October', 'November', 'December'
        ];

        // ── CALENDAR ──
        let calYear, calMonth, selectedTrip = null;

        function initCalendar() {
          calYear = 2026;
          calMonth = 5;
          renderCalendar();
        }

        function updateDates() {
          selectedTrip = null;
          document.getElementById('dateDisplay').textContent = 'Select Date';
          renderCalendar();
          updateSummary();
        }

 function toggleCalendar(e) {
  e && e.stopPropagation();
  const popup = document.getElementById('calendarPopup');
  popup.classList.toggle('hidden');
  if (!popup.classList.contains('hidden')) {
    const card = document.getElementById('dateCard');
    const rect = card.getBoundingClientRect();
    const pw = 300; // popup width from CSS

    // Clamp left so it doesn't go off screen
    let left = rect.left + rect.width / 2 - pw / 2;
    left = Math.max(8, Math.min(left, window.innerWidth - pw - 8));

    popup.style.left = left + 'px';
    if (rect.top < 380) {
      popup.style.top = (rect.bottom + 14) + 'px';
      popup.style.bottom = 'auto';
    } else {
      popup.style.top  = 'auto';
      popup.style.bottom = (window.innerHeight - rect.top + 14) + 'px';
    }

    renderCalendar();
  }
}



        function changeMonth(delta, e) {
          e && e.stopPropagation();
          calMonth += delta;
          if (calMonth > 11) {
            calMonth = 0;
            calYear++;
          }
          if (calMonth < 0) {
            calMonth = 11;
            calYear--;
          }
          renderCalendar();
        }

        function renderCalendar() {
          const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
          document.getElementById('calMonthYear').textContent = `${monthNames[calMonth]} ${calYear}`;
          const grid = document.getElementById('calGrid');
          grid.innerHTML = '';
          const firstDay = new Date(calYear, calMonth, 1).getDay();
          const daysInMonth = new Date(calYear, calMonth + 1, 0).getDate();
          const selectedShip = document.getElementById('selectShip').value;

          const dayMap = {};
          Object.entries(shipData).forEach(([shipKey, data]) => {
            data.trips.forEach((trip, tripIndex) => {
              for (let d = trip.startDay; d <= trip.endDay; d++) {
                if (!dayMap[d]) dayMap[d] = [];
                dayMap[d].push({
                  ship: shipKey,
                  tripIndex,
                  isStart: d === trip.startDay,
                  isEnd: d === trip.endDay
                });
              }
            });
          });

          for (let i = 0; i < firstDay; i++) {
            const b = document.createElement('div');
            b.className = 'cal-day empty';
            grid.appendChild(b);
          }

          for (let day = 1; day <= daysInMonth; day++) {
            const cell = document.createElement('div');
            cell.className = 'cal-day';
            cell.textContent = day;
            if (dayMap[day]) {
              const info = dayMap[day][0];
              cell.classList.add(`trip-${info.ship}`);
              if (info.isStart) cell.classList.add('range-start');
              if (info.isEnd) cell.classList.add('range-end');
              if (selectedTrip && selectedTrip.ship === info.ship && selectedTrip.tripIndex === info.tripIndex && selectedTrip.year === calYear && selectedTrip.month === calMonth)
                cell.classList.add('selected-trip');
              const isMyShip = !selectedShip || info.ship === selectedShip;
              if (isMyShip) {
                cell.classList.add('clickable');
                cell.addEventListener('click', (e) => {
                  e.stopPropagation();
                  selectTrip(info.ship, info.tripIndex, calYear, calMonth);
                });
              }
            }
            grid.appendChild(cell);
          }
        }

        function selectTrip(ship, tripIndex, year, month) {
          selectedTrip = {
            ship,
            tripIndex,
            year,
            month
          };
          const trip = shipData[ship].trips[tripIndex];
          const monthNames = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
          document.getElementById('dateDisplay').textContent = `${monthNames[month]} ${trip.startDay} – ${trip.endDay}, ${year}`;
          if (!document.getElementById('selectShip').value) document.getElementById('selectShip').value = ship;
          renderCalendar();
          updateSummary();
          setTimeout(() => document.getElementById('calendarPopup').classList.add('hidden'), 300);
        }

        document.addEventListener('click', (e) => {
          const popup = document.getElementById('calendarPopup');
          if (!popup.classList.contains('hidden') && !popup.contains(e.target) && !document.getElementById('dateCard').contains(e.target))
            popup.classList.add('hidden');
        });

        document.getElementById('selectShip').addEventListener('change', () => {
          renderCalendar();
          updateSummary();
        });
        initCalendar();

        function changeGuest(type, delta) {
          if (type === 'adult') {
            adults.count = Math.max(1, adults.count + delta);
            document.getElementById('adultCount').textContent = adults.count;
          } else {
            children.count = Math.max(0, children.count + delta);
            document.getElementById('childCount').textContent = children.count;
          }
          updateSummary();
        }

        function calcPrice(tier, adultCount, childCount) {
          if (!tier) return null;
          const p = tierPrices[tier];
          const totalGuests = adultCount + childCount;

          let total = 0;

          if (totalGuests === 2 && childCount === 0) {
            // Exactly 2 adults — use promo
            total = p.promo;
          } else {
            // Per person pricing, children 50% off
            total = (adultCount * p.single) + (childCount * p.single * 0.5);
          }

          return total;
        }

        function formatPrice(amount) {
          return '₱' + amount.toLocaleString('en-PH', {
            minimumFractionDigits: 0
          }) + ' PHP';
        }

        function updateSummary() {
          const tier = document.getElementById('selectTier').value;
          const price = calcPrice(tier, adults.count, children.count);
          document.getElementById('priceDisplay').textContent = price ? formatPrice(price) : '—';
        }

        function handleBook() {
          const ship = document.getElementById('selectShip').value;
          const dateVal = document.getElementById('dateDisplay').textContent;
          const dateText = document.getElementById('dateDisplay').textContent;
          const tier = document.getElementById('selectTier').value;
          const price = calcPrice(tier, adults.count, children.count);

          if (!ship || !dateVal || dateVal === 'Select a Date' || !tier) {
            alert('Please fill in all fields before booking.');
            return;
          }

          const route = shipData[ship].route;
          const tierLabel = tier.charAt(0).toUpperCase() + tier.slice(1);
          const totalGuests = adults.count + children.count;
          const isPromo = adults.count === 2 && children.count === 0;

          document.getElementById('bookingSummaryContent').innerHTML = `
        <div class="summary-row"><span>Ship</span><span>${shipData[ship].label}</span></div>
        <div class="summary-row"><span>Route</span><span>${route}</span></div>
        <div class="summary-row"><span>Date</span><span>${dateText}</span></div>
        <div class="summary-row"><span>Adults</span><span>${adults.count}</span></div>
        <div class="summary-row"><span>Children</span><span>${children.count}</span></div>
        <div class="summary-row"><span>Tier</span><span>${tierLabel}</span></div>
        ${isPromo ? '<div class="summary-row"><span>Pricing</span><span>2-Ticket Promo 🎉</span></div>' : ''}
        <div class="summary-row"><span>Total</span><span>${formatPrice(price)}</span></div>
      `;
    openPayment();
    // start session timer
resetInactivityTimer();
const payBackdrop = document.getElementById('payBackdrop');
payBackdrop.addEventListener('mousemove', resetInactivityTimer);
payBackdrop.addEventListener('keydown', resetInactivityTimer);
payBackdrop.addEventListener('click', resetInactivityTimer);
payBackdrop.addEventListener('touchstart', resetInactivityTimer);
        }

        function closeBookConfirm() {
          document.getElementById('bookConfirmBackdrop').classList.remove('visible');
        }

        document.getElementById('bookConfirmBackdrop').addEventListener('click', (e) => {
          if (e.target === document.getElementById('bookConfirmBackdrop')) closeBookConfirm();
        });

        // ── PAYMENT MODAL ──
        let payAdults = 1;
        let payChildren = 0;

        function openPayment() {
          const ship = document.getElementById('selectShip')?.value || '';
          const tier = document.getElementById('selectTier')?.value || '';
          const dateText = document.getElementById('dateDisplay')?.textContent || '—';

          // Sync values from booking widget
          payAdults = adults.count;
          payChildren = children.count;

          if (ship) document.getElementById('paySelectShip').value = ship;
          if (tier) document.getElementById('paySelectTier').value = tier;
          document.getElementById('payAdultCount').textContent = payAdults;
          document.getElementById('payChildCount').textContent = payChildren;

          payUpdateDates();
          payUpdateBar();
          payRecalc();

          document.getElementById('payBackdrop').classList.add('visible');
        

         resetInactivityTimer();
  const pb = document.getElementById('payBackdrop');
  pb.addEventListener('mousemove', resetInactivityTimer);
  pb.addEventListener('keydown', resetInactivityTimer);
  pb.addEventListener('click', resetInactivityTimer);
  pb.addEventListener('touchstart', resetInactivityTimer);
}

        function closePayment() {
  stopInactivityTimer(); // ← add this line
  document.getElementById('payBackdrop').classList.remove('visible');
}

        function payUpdateDates() {
          const ship = document.getElementById('paySelectShip').value;
          const select = document.getElementById('paySelectDate');
          select.innerHTML = '<option value="">Select Date</option>';
          if (!ship) return;

          const now = new Date();
          const data = shipData[ship];
          for (let m = 0; m < 24; m++) {
            const d = new Date(now.getFullYear(), now.getMonth() + m, 1);
            const month = monthNames[d.getMonth()];
            const year = d.getFullYear();
            data.trips.forEach((trip) => {
              const opt = document.createElement('option');
              opt.textContent = `${month} ${trip.startDay} – ${trip.endDay}, ${year}`;
              select.appendChild(opt);
            });
          }
        }

        function payChangeGuest(type, delta) {
          if (type === 'adult') {
            payAdults = Math.max(1, payAdults + delta);
            document.getElementById('payAdultCount').textContent = payAdults;
            document.getElementById('sumGuests').textContent = payAdults;
          } else {
            payChildren = Math.max(0, payChildren + delta);
            document.getElementById('payChildCount').textContent = payChildren;
          }
          payUpdateBar();
          payRecalc();
        }

        function payUpdateBar() {
          const ship = document.getElementById('paySelectShip').value;
          const tier = document.getElementById('paySelectTier').value;
          const dateText = document.getElementById('paySelectDate').options[document.getElementById('paySelectDate').selectedIndex]?.text || '—';

          document.getElementById('payShipName').textContent = ship ? shipData[ship].label : '—';
          document.getElementById('payRoute').textContent = ship ? shipData[ship].route : '—';
          document.getElementById('payDate').textContent = dateText;
          document.getElementById('payTier').textContent = tier ? tier.charAt(0).toUpperCase() + tier.slice(1) + ' Tier' : '—';
          document.getElementById('payGuests').textContent = `${payAdults} Adult${payAdults > 1 ? 's' : ''}${payChildren > 0 ? `, ${payChildren} Child${payChildren > 1 ? 'ren' : ''}` : ''}`;
        }

        function payRecalc() {
          const tier = document.getElementById('paySelectTier').value;
          payUpdateBar();

          document.getElementById('sumTier').textContent = tier ? tier.charAt(0).toUpperCase() + tier.slice(1) : '—';
          document.getElementById('sumGuests').textContent = payAdults + payChildren;

          if (!tier) {
            document.getElementById('sumSubtotal').textContent = '—';
            document.getElementById('sumTax').textContent = '—';
            document.getElementById('sumTotal').textContent = '—';
            return;
          }

          const subtotal = calcPrice(tier, payAdults, payChildren);
          const tax = subtotal * 0.004;
          const total = subtotal + tax;

          document.getElementById('sumSubtotal').textContent = formatPrice(subtotal);
          document.getElementById('sumTax').textContent = formatPrice(tax);
          document.getElementById('sumTotal').textContent = formatPrice(total);
        }
    function showPayMethod(method) {
      // Hide all fields
      ['Gcash', 'Maya', 'Card'].forEach(m => {
        document.getElementById(`fields${m}`).classList.add('hidden');
      });

      // Hide all logos
      ['Gcash', 'Maya', 'Visa', 'Mastercard', 'Bpi', 'Jcb'].forEach(m => {
        document.getElementById(`logo${m}`).classList.add('hidden');
      });

      // Show relevant fields + logo
      if (method === 'gcash') {
        document.getElementById('fieldsGcash').classList.remove('hidden');
        document.getElementById('logoGcash').classList.remove('hidden');
      } else if (method === 'maya') {
        document.getElementById('fieldsMaya').classList.remove('hidden');
        document.getElementById('logoMaya').classList.remove('hidden');
      } else if (['visa', 'mastercard', 'bpi', 'jcb'].includes(method)) {
        document.getElementById('fieldsCard').classList.remove('hidden');
        document.getElementById(`logo${method.charAt(0).toUpperCase() + method.slice(1)}`).classList.remove('hidden');
      }
    }

    function confirmPayment() {
  const ship = document.getElementById('paySelectShip').value;
  const tier = document.getElementById('paySelectTier').value;
  const dateText = document.getElementById('paySelectDate')
    .options[document.getElementById('paySelectDate').selectedIndex]?.text || '—';
  const nameInput = document.querySelector('#fieldsGcash input, #fieldsMaya input, #fieldsCard input');

  if (!ship || !tier || dateText === 'Select Date') {
    alert('Please complete your booking details before confirming.');
    return;
  }

  const subtotal = calcPrice(tier, payAdults, payChildren);
  const tax = subtotal * 0.004;
  const total = subtotal + tax;

  generateTicket({
    ship:     shipData[ship].label,
    route:    shipData[ship].route,
    tier:     tier.charAt(0).toUpperCase() + tier.slice(1),
    date:     dateText,
    adults:   payAdults,
    children: payChildren,
    name:     nameInput?.value || 'Guest',
    total:    formatPrice(total),
  });
stopInactivityTimer();
  closePayment();
}

        

        function closeSuccess() {
          document.getElementById('successBackdrop').classList.remove('visible');
        }

        document.getElementById('successBackdrop').addEventListener('click', (e) => {
          if (e.target === document.getElementById('successBackdrop')) closeSuccess();
        });

        document.getElementById('payBackdrop').addEventListener('click', (e) => {
          if (e.target === document.getElementById('payBackdrop')) closePayment();
        });

        function generateTicket(d) {
  const ref = 'ALE-' + new Date().getFullYear() + '-' + Math.floor(1000 + Math.random() * 9000);
  const today = new Date();
  const bookingDate = today.toLocaleDateString('en-PH', { year: 'numeric', month: 'long', day: 'numeric' });
  const routeParts = d.route.split('→').map(s => s.trim());
  const port = routeParts[0] || '—';
  const destination = routeParts.slice(1, routeParts.length - 1).join(', ') || routeParts[1] || '—';
  const roomPrefixes = { Premium: 'P', Elite: 'E', Ultimate: 'U' };
  const prefix = roomPrefixes[d.tier] || 'R';
  const roomFloor = { Premium: 1, Elite: 2, Ultimate: 3 }[d.tier] || 1;
  const roomNum = prefix + roomFloor + String(Math.floor(10 + Math.random() * 89));
  const guestStr = d.adults + ' Adult' + (d.adults > 1 ? 's' : '')
    + (d.children > 0 ? ', ' + d.children + ' Child' + (d.children > 1 ? 'ren' : '') : '');

  document.getElementById('tkt-ship').textContent         = 'MV ' + d.ship;
  document.getElementById('tkt-route').textContent        = d.route;
  document.getElementById('tkt-tier').textContent         = d.tier + ' Tier';
  document.getElementById('tkt-tier-label').textContent   = d.tier;
  document.getElementById('tkt-date').textContent         = d.date;
  document.getElementById('tkt-booking-date').textContent = bookingDate;
  document.getElementById('tkt-guests').textContent       = guestStr;
  document.getElementById('tkt-name').textContent         = d.name;
  document.getElementById('tkt-total').textContent        = d.total;
  document.getElementById('tkt-ref').textContent          = ref;
  document.getElementById('tkt-port').textContent         = port;
  document.getElementById('tkt-destination').textContent  = destination;
  document.getElementById('tkt-room').textContent         = roomNum;

  renderBarcode(ref);
  document.getElementById('ticketBackdrop').classList.add('visible');
}

function renderBarcode(ref) {
  const bc = document.getElementById('tkt-barcode');
  bc.innerHTML = '';
  let seed = ref.split('').reduce((a, c) => a + c.charCodeAt(0), 0);
  for (let i = 0; i < 42; i++) {
    seed = (seed * 1103515245 + 12345) & 0x7fffffff;
    const h = 8 + (seed % 30);
    const w = (seed % 3 === 0) ? 4 : 2;
    bc.innerHTML += `<div style="height:${h}px;width:${w}px;background:#0a1628;border-radius:1px;"></div>`;
  }
}

// ── SESSION TIMER ──
let inactivityTimer = null;
let warningTimer = null;

function stopInactivityTimer() {
  clearTimeout(inactivityTimer);
  clearTimeout(warningTimer);
  removeSessionWarning();
}

function resetInactivityTimer() {
  stopInactivityTimer();
  warningTimer = setTimeout(showSessionWarning, 50000);
  inactivityTimer = setTimeout(() => {
    closePayment();
    removeSessionWarning();
    showSessionExpiredAlert();
  }, 60000);
}

function showSessionWarning() {
  const warn = document.createElement('div');
  warn.id = 'sessionWarning';
  warn.style.cssText = `
    position:fixed;bottom:24px;left:50%;transform:translateX(-50%);
    background:#1a3a5c;color:#fff;padding:12px 24px;border-radius:10px;
    font-family:'DM Sans',sans-serif;font-size:0.85rem;z-index:99999;
    box-shadow:0 8px 24px rgba(0,0,0,0.3);border:1px solid #67B5D1;
  `;
  warn.textContent = '⚠️ Your session will expire in 10 seconds due to inactivity.';
  document.body.appendChild(warn);
}

function removeSessionWarning() {
  const warn = document.getElementById('sessionWarning');
  if (warn) warn.remove();
}

function showSessionExpiredAlert() {
  const msg = document.createElement('div');
  msg.style.cssText = `
    position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);
    background:#fff;border:3px solid #c9b99a;border-radius:16px;
    padding:36px 40px;text-align:center;z-index:99999;
    font-family:'DM Sans',sans-serif;box-shadow:0 24px 64px rgba(0,0,0,0.25);
    max-width:360px;width:90%;
  `;
  msg.innerHTML = `
    <div style="font-size:2rem;margin-bottom:12px;">⏰</div>
    <div style="font-family:'Playfair Display',serif;font-size:1.4rem;color:#0a1628;margin-bottom:8px;">Session Expired</div>
    <p style="font-size:0.875rem;color:#555;margin-bottom:20px;">Your payment session timed out due to inactivity.</p>
    <button onclick="this.parentElement.remove()" class="pay-confirm-btn">OK</button>
  `;
  document.body.appendChild(msg);
}
