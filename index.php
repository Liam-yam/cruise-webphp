  <?php
  session_start();
  require_once 'navbar/models.php';

  $siteName = "Alena";
  $tagline = "Pearl of the Orient Sea";
  $pageTitle = "Alena";
  $currentYear = date('Y');

  $ships = [
    'tropical'   => 'Tropical',
    'masquerade' => 'Masquerade',
    'lostcity'   => 'Lost City'
  ];

  $navLinks = [
    'Our Ships'     => 'navbar/ourships/LostCities.php',
    'Book a Cruise' => 'navbar/bookacruise/booking.php',
    'Destinations'  => 'navbar/destination/destination.php',
    'Profile' => 'navbar/profile/profile.php',
    'About'         => '#'
  ];

  $activePage = 'Our Ships';

  $destinations = [
    new Destination('Puerto Princesa', 'static/pp.png',   true),
    new Destination('Cebu',            'static/cebu.png', false),
    new Destination('La Union',        'static/la.png',   false),
  ];

  $tiers = [
    new Tier('Premium',  'static/prem.png'),
    new Tier('Elite',    'static/elite.png'),
    new Tier('Ultimate', 'static/ult.png'),
  ];

  $cruiseShips = [
    new CruiseShip('Tropical',   'static/tropical.png', ['💃 Live Dance', '🎆 Firework Shows', '🏊 Luxury Pool']),
    new CruiseShip('Masquerade', 'static/masq.png',     ['🎭 Live Theater', '🎆 Firework Shows', '🏊 Luxury Pool']),
    new CruiseShip('Lost City',  'static/lost.png',     ['🎸 Live Band', '🎆 Firework Shows', '🏊 Luxury Pool']),
  ];

  $footerLinks = [
    'Holiday Cruises',
    'Philippine Tourism',
    'Philippine Adventures',
    'Connect with Us',
    'About'
  ];

  $slides = [
    'static/cruise1.jpg',
    'static/cruise2.png',
    'static/cruise3.jpg',
    'static/cruise4.avif',
  ];
  ?>


    <!DOCTYPE html>
    <html lang="en">

    <head>
      <meta charset="UTF-8" />
      <meta name="viewport" content="width=device-width, initial-scale=1.0" />
      <title><?php echo $pageTitle; ?></title>
      <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet" />
      <link rel="stylesheet" href="style.css">
    </head>

    <body>

      
      <nav>
        <div class="nav-inner">

          <a href="#" class="logo">
      <img src="static/logo.svg" alt="Logo" class="logo-icon" />
      <div class="logo-text">
        <?php echo $siteName; ?>
        <span><?php echo $tagline; ?></span>
      </div>
    </a>

        <ul class="nav-links">
      <?php foreach ($navLinks as $label => $url): ?>
        <li>
          <a href="<?php echo $url; ?>"
            class="<?php echo ($label === $activePage) ? 'active' : ''; ?>">
            <?php echo $label; ?>
          </a>
        </li>
      <?php endforeach; ?>
    </ul>

          <?php if (isset($_SESSION['user'])): ?>

  <div style="display:flex; align-items:center; gap:12px;">

      <div style="
          display:flex;
          align-items:center;
          gap:10px;
          background:rgba(255,255,255,0.08);
          padding:8px 14px;
          border-radius:30px;
          border:1px solid rgba(255,255,255,0.15);
      ">

          <div style="
              width:34px;
              height:34px;
              border-radius:50%;
              background:#67B5D1;
              display:flex;
              align-items:center;
              justify-content:center;
              color:white;
              font-weight:700;
              font-size:.9rem;
          ">
              <?php echo strtoupper(substr($_SESSION['user'], 0, 1)); ?>
          </div>

          <span style="
              color:white;
              font-size:.88rem;
              font-weight:500;
              max-width:120px;
              overflow:hidden;
              text-overflow:ellipsis;
              white-space:nowrap;
          ">
              <?php echo $_SESSION['user']; ?>
          </span>

      </div>

      <a href="navbar/logout.php" class="btn-signin">
          Log Out
      </a>

  </div>

  <?php else: ?>

  <a href="navbar/login.php" class="btn-signin">
      Sign In
  </a>

  <?php endif; ?>

          <button class="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
          </button>

        </div>
      </nav>

      <section class="hero">

        <div class="slides" id="slides">
      <?php foreach ($slides as $slide): ?>
        <div class="slide" style="background-image: url('<?php echo $slide; ?>');"></div>
      <?php endforeach; ?>

      <div class="slide slide-video">
        <video autoplay muted loop playsinline>
          <source src="static/vid1.mp4" type="video/mp4">
        </video>
      </div>

    </div>
        </div> 
        <div class="cards-wrapper" id="bookingWidget">

          <div class="glass-card">
            <label>Cruise Ship</label>
            <select id="selectShip" onchange="updateDates()">
      <option value="">Select Ship</option>
      <?php foreach ($ships as $key => $label): ?>
        <option value="<?php echo $key; ?>"><?php echo $label; ?></option>
      <?php endforeach; ?>
    </select>
          </div>

          <div class="glass-card" id="dateCard" style="cursor:pointer;position:relative;" onclick="toggleCalendar(event)">
            <label>Trip Date</label>
            <div id="dateDisplay" style="font-size:0.88rem;font-weight:500;color:#fff;min-height:1.2em;">
              Select a Date
            </div>
          </div>

  

          <div class="glass-card guests-card">
            <label>Guests</label>
            <div class="guest-row">
              <span>Adults</span>
              <div class="guest-counter">
                <button onclick="changeGuest('adult', -1)">−</button>
                <span id="adultCount">1</span>
                <button onclick="changeGuest('adult', 1)">+</button>
              </div>
            </div>
            <div class="guest-row">
              <span>Children <small>(50% off)</small></span>
              <div class="guest-counter">
                <button onclick="changeGuest('child', -1)">−</button>
                <span id="childCount">0</span>
                <button onclick="changeGuest('child', 1)">+</button>
              </div>
            </div>
          </div>

          <div class="glass-card">
            <label>Tier</label>
            <select id="selectTier" onchange="updateSummary()">
              <option value="">Select Tier</option>
              <option value="premium">Premium</option>
              <option value="elite">Elite</option>
              <option value="ultimate">Ultimate</option>
            </select>
          </div>

          <div class="glass-card book-card">
            <div id="priceSummary" class="price-summary">
              <span class="price-label">Total</span>
              <span class="price-amount" id="priceDisplay">—</span>
            </div>
            <button class="btn-book" onclick="window.location.href='navbar/bookacruise/booking.php'">Book Now</button>
          </div>

        </div>

        <div class="modal-backdrop" id="bookConfirmBackdrop">
          <div class="modal" id="bookConfirmModal">
            <button class="modal-close" onclick="closeBookConfirm()">✕</button>
            <div class="modal-header">
              <img src="static/logo.svg" alt="Logo" class="modal-logo" />
              <h2 class="modal-title">Booking Summary</h2>
              <p class="modal-sub">Review your trip before confirming</p>
            </div>
            <div id="bookingSummaryContent" class="booking-summary-content"></div>
            <button class="modal-btn" style="margin-top: 16px;">Confirm Booking</button>
          </div>
        </div>

        <div class="dots" id="dots">
          <button class="dot active" onclick="goTo(0)"></button>
          <button class="dot" onclick="goTo(1)"></button>
          <button class="dot" onclick="goTo(2)"></button>
          <button class="dot" onclick="goTo(3)"></button>
          <button class="dot" onclick="goTo(4)"></button>
        </div>
      </section>

      <section class="ships-section">
        <div class="ships-inner">

          <div class="section-header">
            <h2 class="section-title">Our Cruise Ships</h2>
            <p class="section-sub">Surrender to the rhythmic pulse of the Pacific as you chase the horizon across seven thousand islands, where every sunrise unveils a new frontier of emerald peaks and hidden turquoise lagoons.</p>
          </div>

        <div class="ships-grid">

    <?php 
    $shipLinks = [
      'Tropical'   => 'tropical.php',
      'Masquerade' => 'masquerade.php',
      'Lost City'  => 'navbar/ourships/LostCities.php',
    ];
    ?>
    <?php foreach ($cruiseShips as $ship): ?>
      <a href="<?php echo $shipLinks[$ship->getName()]; ?>" class="ship-card">
        <div class="ship-bg" style="background-image: url('<?php echo $ship->getImage(); ?>');"></div>
        <div class="ship-overlay"></div>
        <div class="ship-card-content">
          <span class="ship-name"><?php echo $ship->getName(); ?></span>
          <div class="ship-stars">★★★★★</div>
          <div class="ship-highlights">
            <?php foreach ($ship->getHighlights() as $highlight): ?>
              <span><?php echo $highlight; ?></span>
            <?php endforeach; ?>
          </div>
        </div>
      </a>
  <?php endforeach; ?>
    </div>
        </div>
      </section>

      <!-- ESCAPE WITH US / DESTINATIONS -->
      <section class="destinations-section">
        <div class="destinations-inner">

          <div class="section-header">
            <h2 class="section-title">Escape with Us</h2>
            <p class="section-sub">Surrender to the rhythmic pulse of the Pacific as you chase the horizon across seven thousand islands, where every sunrise unveils a new frontier of emerald peaks and hidden turquoise lagoons.</p>
          </div>

          <div class="destinations-grid">

          <?php foreach ($destinations as $dest): ?>
    <?php if ($dest->isFeatured()): ?>
      <a href="navbar/destination/destination.php" class="dest-card dest-featured">
        <div class="dest-bg" style="background-image: url('<?php echo $dest->getImage(); ?>');"></div>
        <div class="dest-overlay"></div>
        <div class="dest-content">
          <span class="dest-name"><?php echo $dest->getName(); ?></span>
        </div>
      </a>
    <?php endif; ?>
  <?php endforeach; ?>

  <div class="dest-row">
    <?php foreach ($destinations as $dest): ?>
      <?php if (!$dest->isFeatured()): ?>
        <a href="navbar/destination/destination.php" class="dest-card">
          <div class="dest-bg" style="background-image: url('<?php echo $dest->getImage(); ?>');"></div>
          <div class="dest-overlay"></div>
          <div class="dest-content">
            <span class="dest-name"><?php echo $dest->getName(); ?></span>
          </div>
        </a>
      <?php endif; ?>
    <?php endforeach; ?>
  </div>
          </div>
        </div>
      </section>

      <section class="experience-section">
        <div class="experience-inner">

          <div class="experience-text">
            <h2 class="experience-title">An Experience like never before.</h2>
            <p class="experience-sub">Surrender to the rhythmic pulse of the Pacific as you chase the horizon across seven thousand islands, where every sunrise unveils a new frontier of emerald peaks and hidden turquoise lagoons.</p>
          </div>

          <div class="experience-cards">
    <?php foreach ($tiers as $tier): ?>
    <a href="#" class="exp-card">
      <div class="exp-bg" style="background-image: url('<?php echo $tier->getImage(); ?>');"></div>
      <div class="exp-overlay"></div>
      <span class="exp-name"><?php echo $tier->getName(); ?></span>
    </a>
  <?php endforeach; ?>
    </div>
        </div>
      </section>

      <footer class="footer">
        <div class="footer-bg-img"></div>
        <div class="footer-overlay"></div>

        <div class="footer-inner">

          <div class="footer-watermark" aria-hidden="true">
            <span>Find Your Escape</span>
            <span>Find Your Escape</span>
            <span>Find Your Escape</span>
            <span>Find Your Escape</span>
          </div>

          <div class="footer-content">
            <h2 class="footer-heading">Find Your Escape</h2>

        <div class="footer-links">
      <?php for ($col = 0; $col < 2; $col++): ?>
        <div class="footer-col">
          <?php foreach ($footerLinks as $link): ?>
            <a href="#"><?php echo $link; ?></a>
          <?php endforeach; ?>
        </div>
      <?php endfor; ?>
    </div>
          </div>

          <div class="footer-bottom">
            <img src="static/logo.svg" alt="Voyager Cruise Lines" class="footer-logo" />
          <p class="footer-copy">© <?php echo $currentYear; ?> <?php echo $siteName; ?> <?php echo $tagline; ?>. All rights reserved.</p>
          </div>

        </div>
      </footer>


      <div class="pay-backdrop" id="payBackdrop">
        <div class="pay-modal">

          <button class="pay-close" onclick="closePayment()">✕</button>
          <h2 class="pay-title">Secure Payment</h2>

          <div class="pay-booking-bar">
            <div class="pay-booking-left">
              <div class="pay-booking-label">Selected Cruise</div>
              <div class="pay-booking-ship" id="payShipName">—</div>
              <div class="pay-booking-route" id="payRoute">—</div>
              <div class="pay-booking-date">Departure Date: <strong id="payDate">—</strong></div>
            </div>
            <div class="pay-booking-divider"></div>
            <div class="pay-booking-right">
              <div class="pay-booking-tier" id="payTier">—</div>
              <div class="pay-booking-guests" id="payGuests">—</div>
            </div>
          </div>


          <div class="pay-adjust">
            <div class="pay-adjust-field">
              <label>Cruise Ship</label>
              <select id="paySelectShip" onchange="payUpdateDates(); payRecalc()">
                <option value="">Select Ship</option>
                <option value="tropical">Tropical</option>
                <option value="masquerade">Masquerade</option>
                <option value="lostcity">Lost City</option>
              </select>
            </div>
            <div class="pay-adjust-field">
              <label>Trip Date</label>
              <select id="paySelectDate" onchange="payRecalc()">
                <option value="">Select Date</option>
              </select>
            </div>
            <div class="pay-adjust-field">
              <label>Tier</label>
              <select id="paySelectTier" onchange="payRecalc()">
                <option value="">Select Tier</option>
                <option value="premium">Premium</option>
                <option value="elite">Elite</option>
                <option value="ultimate">Ultimate</option>
              </select>
            </div>
            <div class="pay-adjust-field">
              <label>Adults</label>
              <div class="pay-stepper">
                <button onclick="payChangeGuest('adult',-1)">−</button>
                <span id="payAdultCount">1</span>
                <button onclick="payChangeGuest('adult',1)">+</button>
              </div>
            </div>
            <div class="pay-adjust-field">
              <label>Children <small>(50% off)</small></label>
              <div class="pay-stepper">
                <button onclick="payChangeGuest('child',-1)">−</button>
                <span id="payChildCount">0</span>
                <button onclick="payChangeGuest('child',1)">+</button>
              </div>
            </div>
          </div>

          <div class="pay-personal">
            <div class="pay-inline-field">
              <label>Full Name :</label>
              <input type="text" placeholder="Juan dela Cruz" />
            </div>
            <div class="pay-inline-field">
              <label>Email Address :</label>
              <input type="email" placeholder="juan@email.com" />
            </div>
            <div class="pay-inline-field">
              <label>Birthdate :</label>
              <input type="date" />
            </div>
            <div class="pay-inline-field">
              <label>Phone Number :</label>
              <input type="tel" placeholder="+63 9XX XXX XXXX" />
            </div>
            <div class="pay-inline-field full">
              <label>Address :</label>
              <input type="text" placeholder="Street, City, Province" />
            </div>
          </div>

          <div class="pay-bottom">

    <div class="pay-method-section">
      <div class="pay-method-title">Payment Method</div>

      <div class="pay-method-dropdown-wrap">
        <select id="payMethodSelect" onchange="showPayMethod(this.value)">
          <option value="">Select Payment Method</option>
          <option value="gcash">💙 GCash</option>
          <option value="maya">💚 Maya</option>
          <option value="visa">💳 Visa</option>
          <option value="mastercard">💳 Mastercard</option>
          <option value="bpi">🏦 BPI</option>
          <option value="jcb">💳 JCB</option>
        </select>
        <div class="pay-method-logos" id="payMethodLogos">
          <img id="logoGcash" src="static/gcash.png" />
          <img id="logoMaya" src="static/maya.png" />
          <img id="logoVisa" src="static/visa.png" />
          <img id="logoMastercard" src="static/mc.png" />
          <img id="logoBpi" src="static/bpi.png" alt="BPI" class="hidden" />
          <img id="logoJcb" src="static/jcb.png" alt="JCB" class="hidden" />
        </div>
      </div>

      <div class="pay-method-fields hidden" id="fieldsGcash">
        <div class="pay-inline-field">
          <label>Name</label>
          <input type="text" placeholder="Name on GCash" />
        </div>
        <div class="pay-inline-field">
          <label>GCash Number</label>
          <input type="tel" placeholder="09XX XXX XXXX" />
        </div>
        <button class="pay-confirm-inner-btn" onclick="confirmPayment()">CONFIRM</button>
      </div>

      <div class="pay-method-fields hidden" id="fieldsMaya">
        <div class="pay-inline-field">
          <label>Maya Name</label>
          <input type="text" placeholder="Name on Maya" />
        </div>
        <div class="pay-inline-field">
          <label>Maya Number</label>
          <input type="tel" placeholder="09XX XXX XXXX" />
        </div>
        <button class="pay-confirm-inner-btn" onclick="confirmPayment()">CONFIRM</button>
      </div>

      <div class="pay-method-fields hidden" id="fieldsCard">
        <div class="pay-inline-field">
          <label>Cardholder Name</label>
          <input type="text" placeholder="Name on card" />
        </div>
        <div class="pay-inline-field">
          <label>Card Number</label>
          <input type="text" placeholder="XXXX XXXX XXXX XXXX" maxlength="19" />
        </div>
        <div class="pay-inline-field">
          <label>Expiry</label>
          <input type="text" placeholder="MM/YY" maxlength="5" style="max-width:90px" />
          <label style="margin-left:12px">CVV</label>
          <input type="text" placeholder="XXX" maxlength="3" style="max-width:70px" />
        </div>
        <button class="pay-confirm-inner-btn" onclick="confirmPayment()">CONFIRM</button>
      </div>

    </div>

            <div class="pay-right">
              <div class="pay-summary-title">Payment Summary</div>
              <div class="pay-sum-row">
                <span>Ticket Type</span>
                <span id="sumTier">—</span>
              </div>
              <div class="pay-sum-row">
                <span>Guest Count</span>
                <div class="pay-stepper small">
                  <button onclick="payChangeGuest('adult',-1)">−</button>
                  <span id="sumGuests">1</span>
                  <button onclick="payChangeGuest('adult',1)">+</button>
                </div>
              </div>
              <div class="pay-sum-row">
                <span>Subtotal</span>
                <span id="sumSubtotal">—</span>
              </div>
              <div class="pay-sum-row">
                <span>Taxes & Fees</span>
                <span id="sumTax">—</span>
              </div>
              <div class="pay-sum-divider"></div>
              <div class="pay-sum-row">
                <span>Promo Code</span>
                <input class="promo-input" type="text" placeholder="Enter code" id="promoCode" />
              </div>
              <div class="pay-sum-divider"></div>
              <div class="pay-sum-row total">
                <span>Total</span>
                <span id="sumTotal">—</span>
              </div>
            </div>

          </div>
        </div>
      </div>

      <div class="pay-backdrop" id="successBackdrop">
        <div class="success-modal">
          <div class="success-icon">✓</div>
          <h2 class="success-title">Booking Confirmed!</h2>
          <p class="success-sub">Your cruise has been booked successfully. A confirmation will be sent to your email.</p>
          <div id="successDetails" class="success-details"></div>
          <button class="pay-confirm-btn" onclick="closeSuccess()" style="margin-top:24px;">Done</button>
        </div>
      </div>

              <div id="calendarPopup" class="cal-popup hidden">
            <div class="cal-header">
              <button class="cal-nav" onclick="changeMonth(-1,event)">&#8249;</button>
              <span id="calMonthYear"></span>
              <button class="cal-nav" onclick="changeMonth(1,event)">&#8250;</button>
            </div>
            <div class="cal-weekdays">
              <span>S</span><span>M</span><span>T</span><span>W</span><span>T</span><span>F</span><span>S</span>
            </div>
            <div class="cal-grid" id="calGrid"></div>
            <div class="cal-legend">
              <div class="leg-item"><span class="leg-dot" style="background:#F5C518;"></span> Tropical</div>
              <div class="leg-item"><span class="leg-dot" style="background:#D94040;"></span> Masquerade</div>
              <div class="leg-item"><span class="leg-dot" style="background:#1B3F7A;"></span> Lost City</div>
            </div>
          </div>

          <div class="pay-backdrop" id="ticketBackdrop">
  <div style="background:#fff;border-radius:20px;width:100%;max-width:560px;overflow:hidden;border:3px solid #c9b99a;transform:translateY(30px);transition:transform 0.35s cubic-bezier(0.77,0,0.18,1);">
    <div style="background:#0a1628;color:#fff;padding:1.25rem 1.5rem;display:flex;align-items:center;justify-content:space-between;">
      <div style="font-family:'Playfair Display',serif;font-size:1.1rem;">
        Alena
        <span style="display:block;font-size:0.65rem;font-family:'DM Sans',sans-serif;color:rgba(255,255,255,0.5);letter-spacing:0.15em;text-transform:uppercase;">Pearl of the Orient Sea</span>
      </div>
      <span id="tkt-tier" style="background:#67B5D1;color:#fff;font-size:0.72rem;padding:4px 14px;border-radius:20px;letter-spacing:0.05em;"></span>
    </div>
    <div style="padding:1.25rem 1.5rem 0.75rem;">
      <div id="tkt-ship" style="font-family:'Playfair Display',serif;font-size:1.5rem;color:#0a1628;margin-bottom:3px;"></div>
      <div id="tkt-route" style="font-size:0.8rem;color:#888;"></div>
    </div>
    <div style="padding:0 1.5rem 1rem;display:grid;grid-template-columns:1fr 1fr 1fr;gap:14px;">
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Passenger</span><strong id="tkt-name" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Booking Date</span><strong id="tkt-booking-date" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Departure Date</span><strong id="tkt-date" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Port of Origin</span><strong id="tkt-port" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Destination</span><strong id="tkt-destination" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Guests</span><strong id="tkt-guests" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Room No.</span><strong id="tkt-room" style="font-size:0.85rem;color:#0a1628;"></strong></div>
      <div><span style="display:block;font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:3px;">Cabin Tier</span><strong id="tkt-tier-label" style="font-size:0.85rem;color:#0a1628;"></strong></div>
    </div>
    <div style="display:flex;align-items:center;margin:0 -1px;">
      <div style="width:18px;height:18px;border-radius:50%;background:#f0ede6;margin-left:-9px;flex-shrink:0;"></div>
      <div style="flex:1;border-top:2px dashed #ddd;"></div>
      <div style="width:18px;height:18px;border-radius:50%;background:#f0ede6;margin-right:-9px;flex-shrink:0;"></div>
    </div>
    <div style="padding:1rem 1.5rem;display:flex;justify-content:space-between;align-items:center;">
      <div>
        <div style="font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;">Total Paid</div>
        <div id="tkt-total" style="font-family:'Playfair Display',serif;font-size:1.3rem;color:#0a1628;margin-top:2px;"></div>
      </div>
      <div style="text-align:right;">
        <div style="font-size:0.68rem;color:#888;text-transform:uppercase;letter-spacing:0.08em;">Booking Ref</div>
        <code id="tkt-ref" style="font-size:0.85rem;color:#0a1628;letter-spacing:0.08em;"></code>
        <div id="tkt-barcode" style="display:flex;align-items:flex-end;gap:1.5px;height:32px;margin-top:4px;"></div>
      </div>
    </div>
    <div style="padding:0 1.5rem 1.5rem;display:flex;gap:10px;">
      <button onclick="window.print()" class="pay-confirm-btn">Print Ticket</button>
      <button onclick="document.getElementById('ticketBackdrop').classList.remove('visible')" class="pay-confirm-btn" style="background:#c9b99a;color:#1a3a5c;">Done</button>
    </div>
  </div>
</div>
      <script src="script.js"></script>
</body>

    </html>
