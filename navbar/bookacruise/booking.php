<?php
session_start();
set_include_path(dirname(__DIR__, 2) . PATH_SEPARATOR . get_include_path());
include_once "navbar/bookacruise/booking-data.php";
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="styles.css">
    <script src="scripts.js" defer></script>
</head>

<body>

<nav>
    <div class="nav-inner">

        <a href="../../index.php" class="logo">
            <img src="assets/logo.svg" alt="Logo" class="logo-icon">

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

                <a href="../profile/profile.php" style="text-decoration:none; color:inherit;">

                <div style="
                    display:flex;
                    align-items:center;
                    gap:10px;
                    background:rgba(255,255,255,0.08);
                    padding:8px 14px;
                    border-radius:30px;
                    border:1px solid rgba(255,255,255,0.15);
                    cursor:pointer;
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

                </a>

                <a href="../logout.php" class="btn-signin">Log Out</a>

            </div>

        <?php else: ?>

            <a href="../login.php" class="btn-signin">Sign In</a>

        <?php endif; ?>

        <button class="hamburger" aria-label="Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>

    </div>
</nav>

<header class="hero">

    <img src="assets/cruise2.png"
         alt="Cruise ship at sunset"
         class="hero-image">

    <div class="hero-title">
        Book Your Wanted Cruise
    </div>

    <section class="booking-wrap"
             aria-label="Cruise booking filters">

        <form class="booking-card"
              method="post"
              action="booking.php">

            <!-- SHIP -->
            <label class="booking-field">

                <span>Cruise Ship:</span>

                <select name="cruise_ship"
                        id="cruiseShip"
                        required>

                    <option value="">
                        Select Ship
                    </option>

                    <?php foreach ($ships as $ship): ?>

                        <option value="<?php echo $ship; ?>"
                            <?php echo $selectedShip == $ship ? "selected" : ""; ?>>

                            <?php echo $ship; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <!-- DATE -->
            <div class="booking-field date-field"
                 id="dateCard"
                 role="button"
                 tabindex="0"
                 aria-haspopup="dialog"
                 aria-expanded="false">

                <span>Trip Date:</span>

                <div id="dateDisplay" class="date-display">
                    Select Date
                </div>

                <input type="hidden"
                       name="trip_date"
                       id="tripDate"
                       value="">

            </div>

            <!-- GUESTS -->
            <label class="booking-field guest-field">

                <span>Guest:</span>

                <div class="guest-row">

                    <!-- ADULT -->
                    <div class="guest-control">

                        <small>Adults</small>

                        <div class="guest-stepper">

                            <button type="button"
                                    class="guest-btn"
                                    data-type="adult"
                                    data-action="minus">

                                -

                            </button>

                            <input type="number"
                                   name="adults"
                                   id="adultCount"
                                   value="1"
                                   min="1"
                                   readonly>

                            <button type="button"
                                    class="guest-btn"
                                    data-type="adult"
                                    data-action="plus">

                                +

                            </button>

                        </div>

                    </div>

                    <!-- CHILD -->
                    <div class="guest-control">

                        <small>
                            Children
                            <span>(50% off)</span>
                        </small>

                        <div class="guest-stepper">

                            <button type="button"
                                    class="guest-btn"
                                    data-type="child"
                                    data-action="minus">

                                -

                            </button>

                            <input type="number"
                                   name="children"
                                   id="childCount"
                                   value="0"
                                   min="0"
                                   readonly>

                            <button type="button"
                                    class="guest-btn"
                                    data-type="child"
                                    data-action="plus">

                                +

                            </button>

                        </div>

                    </div>

                </div>

            </label>

            <!-- TIER -->
            <label class="booking-field small">

                <span>Tier:</span>

                <select name="tier"
                        id="tierSelect"
                        required>

                    <option value="">
                        Select Tier
                    </option>

                    <?php foreach ($tierPrices as $tier => $price): ?>

                        <option value="<?php echo $tier; ?>"
                            <?php echo $selectedTier == $tier ? "selected" : ""; ?>>

                            <?php echo $tier; ?>

                        </option>

                    <?php endforeach; ?>

                </select>

            </label>

            <!-- TOTAL -->
            <div class="total-card">

                <span>Total</span>

                <strong id="totalPrice">
                    &#8369;0
                </strong>

                <input type="hidden"
                       name="total_price"
                       id="totalPriceInput"
                       value="0">

                <button class="date-button"
                        type="submit">

                    BOOK

                </button>

            </div>

        </form>

    </section>

    <div id="calendarPopup" class="cal-popup hidden">
        <div class="cal-header">
            <button type="button" class="cal-nav" id="prevMonth">&#8249;</button>
            <strong id="calMonthYear"></strong>
            <button type="button" class="cal-nav" id="nextMonth">&#8250;</button>
        </div>

        <div class="cal-weekdays">
            <span>Sun</span>
            <span>Mon</span>
            <span>Tue</span>
            <span>Wed</span>
            <span>Thu</span>
            <span>Fri</span>
            <span>Sat</span>
        </div>

        <div class="cal-grid" id="calGrid"></div>

        <div class="cal-legend">
            <div class="leg-item"><span class="leg-dot trip-tropical"></span> Tropical</div>
            <div class="leg-item"><span class="leg-dot trip-masquerade"></span> Masquerade</div>
            <div class="leg-item"><span class="leg-dot trip-lost-cities"></span> Lost Cities</div>
        </div>
    </div>

</header>

<main>

<?php if (!empty($bookingMessage)): ?>
    <section class="booking-message">
        <?php echo htmlspecialchars($bookingMessage); ?>
    </section>
<?php endif; ?>

<section class="packages"
         aria-label="Cruise ticket packages">

    <img src="assets/cabinbg.png"
         alt="Cruise balcony ocean view"
         class="packages-bg">

    <div class="rule"></div>

    <?php
    $packageNumber = 0;

    foreach ($packages as $tier => $package):

        $packageNumber++;

        $price = $tierPrices[$tier];
        $promoPrice = $promoPrices[$tier];

        $isLastPackage =
            $packageNumber == count($packages);

        $packageClass =
            $package["class"] .
            ($isLastPackage ? " last-package" : "");
    ?>

    <article class="package <?php echo $packageClass; ?>">

        <div class="cabin-image">

            <img src="<?php echo $package["image"]; ?>"
                 alt="<?php echo $package["alt"]; ?>">

        </div>

        <div class="package-info">

            <h2>
                <?php echo $package["grade"] . " - " . $tier; ?>
            </h2>

            <ul>

                <?php foreach ($package["features"] as $feature): ?>

                    <li>
                        <?php echo $feature; ?>
                    </li>

                <?php endforeach; ?>

            </ul>

        </div>

        <div class="package-price">

            <span>
                Price per Guest**
            </span>

            <p>
                <?php echo formatPeso($price); ?>
                <small>PHP</small>
            </p>

            <strong>
                PROMO PRICE FOR 2!!
            </strong>

            <p class="promo-price">

                <?php echo formatPeso($promoPrice); ?>

                <small>PHP</small>

            </p>

        </div>

    </article>

    <?php endforeach; ?>

</section>

</main>

<?php if (!empty($pendingBooking)): ?>
    <div class="pay-backdrop visible" id="payBackdrop">
        <form method="post" action="booking.php" class="pay-modal">
            <input type="hidden" name="confirm_payment" value="1">

            <button type="button"
                    class="pay-close"
                    onclick="document.getElementById('payBackdrop').classList.remove('visible'); document.body.style.overflow=''">
                &times;
            </button>

            <h2 class="pay-title">Secure Payment</h2>

            <div class="pay-booking-bar">
                <div class="pay-booking-left">
                    <div class="pay-booking-label">Selected Cruise</div>
                    <div class="pay-booking-ship"><?php echo htmlspecialchars($pendingBooking["ship"]); ?></div>
                    <div class="pay-booking-route"><?php echo htmlspecialchars($pendingBooking["trip_date"]); ?></div>
                    <div class="pay-booking-date">
                        Departure Date:
                        <strong><?php echo htmlspecialchars($pendingBooking["departure_date"]); ?></strong>
                    </div>
                </div>

                <div class="pay-booking-divider"></div>

                <div class="pay-booking-right">
                    <div class="pay-booking-tier"><?php echo htmlspecialchars($pendingBooking["tier"]); ?> Tier</div>
                    <div class="pay-booking-guests">
                        <?php echo (int) $pendingBooking["adults"]; ?> Adult<?php echo ((int) $pendingBooking["adults"] > 1) ? "s" : ""; ?>
                        <?php if ((int) $pendingBooking["children"] > 0): ?>
                            , <?php echo (int) $pendingBooking["children"]; ?> Child<?php echo ((int) $pendingBooking["children"] === 1) ? "" : "ren"; ?>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="pay-personal">
                <div class="pay-inline-field">
                    <label>Full Name :</label>
                    <input type="text"
                           name="payer_name"
                           value="<?php echo htmlspecialchars($_SESSION["user"] ?? ""); ?>"
                           required>
                </div>

                <div class="pay-inline-field">
                    <label>Email Address :</label>
                    <input type="email"
                           value="<?php echo htmlspecialchars($_SESSION["user_email"] ?? ""); ?>"
                           readonly>
                </div>
            </div>

            <div class="pay-bottom">
                <div class="pay-method-section">
                    <div class="pay-method-title">Payment Method</div>

                    <div class="pay-method-dropdown-wrap">
                        <select name="payment_method" id="payMethodSelect" required>
                            <option value="">Select Payment Method</option>
                            <option value="GCash">GCash</option>
                            <option value="Maya">Maya</option>
                            <option value="Visa">Visa</option>
                            <option value="Mastercard">Mastercard</option>
                            <option value="BPI">BPI</option>
                            <option value="JCB">JCB</option>
                        </select>

                        <div class="pay-method-logos">
                            <img src="../../static/gcash.png" alt="GCash">
                            <img src="../../static/maya.png" alt="Maya">
                            <img src="../../static/visa.png" alt="Visa">
                            <img src="../../static/mc.png" alt="Mastercard">
                        </div>
                    </div>

                    <div class="pay-method-fields hidden" data-payment-fields="GCash">
                        <div class="pay-inline-field">
                            <label>GCash Name</label>
                            <input type="text" name="gcash_name" placeholder="Name on GCash" disabled>
                        </div>
                        <div class="pay-inline-field">
                            <label>GCash Number</label>
                            <input type="tel" name="gcash_number" placeholder="09XX XXX XXXX" pattern="09[0-9]{9}" disabled>
                        </div>
                        <div class="pay-inline-field">
                            <label>Reference No.</label>
                            <input type="text" name="gcash_reference" id="gcashReferenceInput"
                                   placeholder="GCash receipt/reference (13 digits)"
                                   maxlength="13"
                                   inputmode="numeric"
                                   pattern="[0-9]{13}"
                                   title="Reference number must be exactly 13 digits"
                                   disabled>
                            <small id="gcashRefError" class="pay-field-error" style="display:none;">
                                
                            </small>
                        </div>
                    </div>

                    <div class="pay-method-fields hidden" data-payment-fields="Maya">
                        <div class="pay-inline-field">
                            <label>Maya Name</label>
                            <input type="text" name="maya_name" placeholder="Name on Maya" disabled>
                        </div>
                        <div class="pay-inline-field">
                            <label>Maya Number</label>
                            <input type="tel" name="maya_number" placeholder="09XX XXX XXXX" pattern="09[0-9]{9}" disabled>
                        </div>
                        <div class="pay-inline-field">
                            <label>Reference No.</label>
                            <input type="text" name="maya_reference" id="mayaReferenceInput"
                                   placeholder="Maya receipt/reference (12 alphanumeric)"
                                   maxlength="12"
                                   pattern="[A-Za-z0-9]{12}"
                                   title="Reference number must be exactly 12 alphanumeric characters"
                                   disabled>
                            <small id="mayaRefError" class="pay-field-error" style="display:none;">
                                
                            </small>
                        </div>
                    </div>

                    <div class="pay-method-fields hidden" data-payment-fields="Visa Mastercard JCB">
                        <div class="pay-inline-field">
                            <label>Cardholder</label>
                            <input type="text" name="card_name" placeholder="Name on card" disabled>
                        </div>
                        <div class="pay-inline-field">
                            <label>Card Number</label>
                            <input type="text" name="card_number" placeholder="XXXX XXXX XXXX XXXX" inputmode="numeric" minlength="13" maxlength="19" disabled>
                        </div>
                        <div class="pay-inline-field pay-card-short-fields">
                            <label>Expiry</label>
                            <input type="text" name="card_expiry" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/[0-9]{2}" disabled>
                            <label>CVV</label>
                            <input type="password" name="card_cvv" placeholder="XXX" pattern="[0-9]{3,4}" maxlength="4" disabled>
                        </div>
                    </div>

                    <div class="pay-method-fields hidden" data-payment-fields="BPI">
                        <div class="pay-inline-field">
                            <label>Cardholder</label>
                            <input type="text" name="bpi_card_name" placeholder="Name on card" disabled>
                        </div>
                        <div class="pay-inline-field">
                            <label>Card Number</label>
                            <input type="text" name="bpi_card_number" placeholder="XXXX XXXX XXXX XXXX" inputmode="numeric" minlength="13" maxlength="19" disabled>
                        </div>
                        <div class="pay-inline-field pay-card-short-fields">
                            <label>Expiry</label>
                            <input type="text" name="bpi_card_expiry" placeholder="MM/YY" pattern="(0[1-9]|1[0-2])\/[0-9]{2}" disabled>
                            <label>CVV</label>
                            <input type="password" name="bpi_card_cvv" placeholder="XXX" pattern="[0-9]{3,4}" maxlength="4" disabled>
                        </div>
                    </div>
                </div>

                <div class="pay-right">
                    <div class="pay-summary-title">Payment Summary</div>
                    <div class="pay-sum-row">
                        <span>Ticket Type</span>
                        <span><?php echo htmlspecialchars($pendingBooking["tier"]); ?></span>
                    </div>
                    <div class="pay-sum-row">
                        <span>Guest Count</span>
                        <span><?php echo (int) $pendingBooking["adults"] + (int) $pendingBooking["children"]; ?></span>
                    </div>
                    <div class="pay-sum-row">
                        <span>Subtotal</span>
                        <span><?php echo formatPeso($pendingBooking["total"]); ?></span>
                    </div>
                    <div class="pay-sum-row">
                        <span>Taxes & Fees</span>
                        <span>Included</span>
                    </div>
                    <div class="pay-sum-divider"></div>
                    <div class="pay-sum-row total">
                        <span>Total</span>
                        <span><?php echo formatPeso($pendingBooking["total"]); ?></span>
                    </div>

                    <button type="submit" class="pay-confirm-btn">CONFIRM PAYMENT</button>
                </div>
            </div>
        </form>
    </div>
<?php endif; ?>

<?php
$ticketConfigs = [
    "Tropical" => [
        "image" => "assets/tickets/tp-ticket.png",
        "theme" => "tropical",
        "to" => "Tropical Isles"
    ],
    "Masquerade" => [
        "image" => "assets/tickets/mq-ticket.png",
        "theme" => "masquerade",
        "to" => "Masquerade Bay"
    ],
    "Lost Cities" => [
        "image" => "assets/tickets/lc-ticket.png",
        "theme" => "lost-cities",
        "to" => "Lost Cities"
    ]
];

if (!empty($paidTicket)):
    $ticketShip = $paidTicket["ship"];
    $ticketConfig = $ticketConfigs[$ticketShip] ?? $ticketConfigs["Lost Cities"];
    $ticketTitle = "Paglaot - " . strtoupper($ticketShip);
    $ticketType = $paidTicket["tier"] . " Tier";
    $ticketName = $paidTicket["user_name"];
    $ticketFrom = "Manila Port";
    $ticketTo = $ticketConfig["to"];
    $ticketRoom = "D" . (((int) $paidTicket["order_no"] % 8) + 1) . "-" . str_pad((((int) $paidTicket["order_no"] % 900) + 100), 3, "0", STR_PAD_LEFT);
    $ticketIssued = date("M d, Y h:i A", strtotime($paidTicket["issued_at"]));
    $ticketGuests = (int) $paidTicket["adults"] + (int) $paidTicket["children"];
    $ticketDownloadName = "paglaot-" . strtolower(str_replace(" ", "-", $ticketShip)) . "-ticket-" . $paidTicket["order_no"] . ".png";
?>
    <div class="ticket-backdrop visible ticket-theme-<?php echo htmlspecialchars($ticketConfig["theme"]); ?>" id="ticketBackdrop">
        <div class="ticket-shell">
            <button type="button"
                    class="ticket-close"
                    data-ticket-close
                    disabled
                    aria-label="Close ticket">
                &times;
            </button>

            <article class="boarding-ticket"
                     id="paidTicket"
                     data-ticket-image="<?php echo htmlspecialchars($ticketConfig["image"]); ?>"
                     data-ticket-title="<?php echo htmlspecialchars($ticketTitle); ?>"
                     data-ticket-ship="<?php echo htmlspecialchars(strtoupper($ticketShip)); ?>"
                     data-ticket-name="<?php echo htmlspecialchars($ticketName); ?>"
                     data-ticket-type="<?php echo htmlspecialchars($ticketType); ?>"
                     data-ticket-from="<?php echo htmlspecialchars($ticketFrom); ?>"
                     data-ticket-to="<?php echo htmlspecialchars($ticketTo); ?>"
                     data-ticket-room="<?php echo htmlspecialchars($ticketRoom); ?>"
                     data-ticket-issued="<?php echo htmlspecialchars($ticketIssued); ?>"
                     data-ticket-departure="<?php echo htmlspecialchars($paidTicket["departure_date"]); ?>"
                     data-ticket-order="<?php echo htmlspecialchars("ORD-" . str_pad($paidTicket["order_no"], 5, "0", STR_PAD_LEFT)); ?>"
                     data-ticket-guests="<?php echo htmlspecialchars($ticketGuests . " Guest" . ($ticketGuests === 1 ? "" : "s")); ?>"
                     data-ticket-logo="assets/tickets/logo.svg"
                     data-ticket-download="<?php echo htmlspecialchars($ticketDownloadName); ?>">
                <div class="ticket-ribbon">BOARDING PASS</div>

                <div class="ticket-photo" style="background-image: url('<?php echo htmlspecialchars($ticketConfig["image"]); ?>');">
                    <h2><?php echo htmlspecialchars($ticketTitle); ?></h2>
                </div>

                <div class="ticket-info">
                    <img src="assets/tickets/logo.svg"
                         alt=""
                         class="ticket-watermark"
                         aria-hidden="true">

                    <div class="ticket-info-top">
                        <h3><?php echo htmlspecialchars(strtoupper($ticketShip)); ?></h3>
                        <div class="ticket-barcode" aria-hidden="true"></div>
                    </div>

                    <div class="ticket-field wide">
                        <span>Name</span>
                        <strong><?php echo htmlspecialchars($ticketName); ?></strong>
                    </div>

                    <div class="ticket-field">
                        <span>Ticket Type</span>
                        <strong><?php echo htmlspecialchars($ticketType); ?></strong>
                    </div>

                    <div class="ticket-route">
                        <div class="ticket-field">
                            <span>From</span>
                            <strong><?php echo htmlspecialchars($ticketFrom); ?></strong>
                        </div>

                        <div class="ticket-field">
                            <span>To</span>
                            <strong><?php echo htmlspecialchars($ticketTo); ?></strong>
                        </div>
                    </div>

                    <div class="ticket-field room">
                        <span>Room Number:</span>
                        <strong><?php echo htmlspecialchars($ticketRoom); ?></strong>
                    </div>

                    <p>Date and Time Issued: <strong><?php echo htmlspecialchars($ticketIssued); ?></strong></p>
                    <p>Departure Date: <strong><?php echo htmlspecialchars($paidTicket["departure_date"]); ?></strong></p>
                    <p>Order No: <strong><?php echo htmlspecialchars("ORD-" . str_pad($paidTicket["order_no"], 5, "0", STR_PAD_LEFT)); ?></strong></p>
                    <p>Guests: <strong><?php echo htmlspecialchars($ticketGuests); ?></strong></p>
                </div>
            </article>

            <button type="button" class="ticket-download" id="ticketDownload">
                Download
            </button>
        </div>
    </div>
<?php endif; ?>

</body>
</html>
