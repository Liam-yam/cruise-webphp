<?php
require_once __DIR__ . "/../db.php";

$message = $_GET["message"] ?? "";
$messageType = $_GET["type"] ?? "success";
$scheduleStatuses = ["active", "suspended"];
$tierStatuses = ["active", "suspended"];

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, "UTF-8");
}

function peso($amount) {
    return "PHP " . number_format((float) $amount, 2);
}

function clean_decimal($value) {
    $value = preg_replace("/[^0-9.]/", "", (string) $value);
    return $value === "" ? 0 : (float) $value;
}

function redirect_admin($type, $message) {
    header("Location: admin.php?type=" . urlencode($type) . "&message=" . urlencode($message));
    exit();
}

if (($_SERVER["REQUEST_METHOD"] ?? "GET") === "POST") {
    $action = $_POST["action"] ?? "";

    if ($action === "add_schedule" || $action === "update_schedule") {
        $ticketNo = (int) ($_POST["ticket_no"] ?? 0);
        $ship = trim($_POST["cruise_ship"] ?? "");
        $itinerary = trim($_POST["itinerary"] ?? "");
        $arrivalDate = trim($_POST["arrival_date"] ?? "");
        $departureDate = trim($_POST["departure_date"] ?? "");
        $roomNo = trim($_POST["room_no"] ?? "");
        $status = $_POST["status"] ?? "active";

        if ($ship === "" || $arrivalDate === "" || $departureDate === "" || $roomNo === "" || !in_array($status, $scheduleStatuses, true)) {
            redirect_admin("danger", "Please complete all required ship schedule fields.");
        }

        if ($action === "add_schedule") {
            $tierMarker = "SCHEDULE";
            $basePrice = 0.00;
            $promoPrice = null;
            $stmt = $conn->prepare(
                "INSERT INTO tbl_ticket
                 (cruise_ship, itinerary, ticket_tier, arrival_date, departure_date, room_no, base_price, promo_price, status)
                 VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)"
            );
            $stmt->bind_param("ssssssdds", $ship, $itinerary, $tierMarker, $arrivalDate, $departureDate, $roomNo, $basePrice, $promoPrice, $status);
        } else {
            $stmt = $conn->prepare(
                "UPDATE tbl_ticket
                    SET cruise_ship = ?, itinerary = ?, arrival_date = ?, departure_date = ?, room_no = ?, status = ?
                  WHERE ticket_no = ?"
            );
            $stmt->bind_param("ssssssi", $ship, $itinerary, $arrivalDate, $departureDate, $roomNo, $status, $ticketNo);
        }

        if (!$stmt || !$stmt->execute()) {
            redirect_admin("danger", "Ship schedule could not be saved. Check for duplicate ship/date values.");
        }

        redirect_admin("success", $action === "add_schedule" ? "Ship schedule added." : "Ship schedule updated.");
    }

    if ($action === "delete_schedule") {
        $ticketNo = (int) ($_POST["ticket_no"] ?? 0);
        $countStmt = $conn->prepare("SELECT COUNT(*) AS booking_count FROM tbl_booking WHERE ticket_no = ?");
        $countStmt->bind_param("i", $ticketNo);
        $countStmt->execute();
        $bookingCount = (int) $countStmt->get_result()->fetch_assoc()["booking_count"];

        if ($bookingCount > 0) {
            $stmt = $conn->prepare("UPDATE tbl_ticket SET status = 'suspended' WHERE ticket_no = ?");
            $stmt->bind_param("i", $ticketNo);
            $stmt->execute();
            redirect_admin("success", "Schedule has bookings, so it was suspended instead of deleted.");
        }

        $stmt = $conn->prepare("DELETE FROM tbl_ticket WHERE ticket_no = ?");
        $stmt->bind_param("i", $ticketNo);
        $stmt->execute();
        redirect_admin("success", "Ship schedule deleted.");
    }

    if ($action === "ship_status") {
        $ship = trim($_POST["cruise_ship"] ?? "");
        $status = $_POST["status"] ?? "";

        if ($ship === "" || !in_array($status, ["active", "suspended"], true)) {
            redirect_admin("danger", "Invalid ship status request.");
        }

        $stmt = $conn->prepare("UPDATE tbl_ticket SET status = ? WHERE cruise_ship = ?");
        $stmt->bind_param("ss", $status, $ship);
        $stmt->execute();
        redirect_admin("success", $status === "active" ? "Ship reactivated." : "Ship suspended.");
    }

    if ($action === "add_tier" || $action === "update_tier") {
        $tierId = (int) ($_POST["tier_id"] ?? 0);
        $tierName = trim($_POST["tier_name"] ?? "");
        $basePrice = clean_decimal($_POST["base_price"] ?? "0");
        $promoRaw = trim($_POST["promo_price"] ?? "");
        $promoPrice = $promoRaw === "" ? null : clean_decimal($promoRaw);
        $status = $_POST["status"] ?? "active";

        if ($tierName === "" || $basePrice <= 0 || !in_array($status, $tierStatuses, true)) {
            redirect_admin("danger", "Please complete all required tier fields.");
        }

        if ($action === "add_tier") {
            $stmt = $conn->prepare(
                "INSERT INTO tbl_tier (tier_name, base_price, promo_price, status)
                 VALUES (?, ?, ?, ?)"
            );
            $stmt->bind_param("sdds", $tierName, $basePrice, $promoPrice, $status);
        } else {
            $stmt = $conn->prepare(
                "UPDATE tbl_tier
                    SET tier_name = ?, base_price = ?, promo_price = ?, status = ?
                  WHERE tier_id = ?"
            );
            $stmt->bind_param("sddsi", $tierName, $basePrice, $promoPrice, $status, $tierId);
        }

        if (!$stmt || !$stmt->execute()) {
            redirect_admin("danger", "Tier could not be saved. Check for duplicate tier names.");
        }

        redirect_admin("success", $action === "add_tier" ? "Tier added." : "Tier updated.");
    }

    if ($action === "delete_tier") {
        $tierId = (int) ($_POST["tier_id"] ?? 0);
        $countStmt = $conn->prepare("SELECT COUNT(*) AS booking_count FROM tbl_booking WHERE tier_id = ?");
        $countStmt->bind_param("i", $tierId);
        $countStmt->execute();
        $bookingCount = (int) $countStmt->get_result()->fetch_assoc()["booking_count"];

        if ($bookingCount > 0) {
            $stmt = $conn->prepare("UPDATE tbl_tier SET status = 'suspended' WHERE tier_id = ?");
            $stmt->bind_param("i", $tierId);
            $stmt->execute();
            redirect_admin("success", "Tier has bookings, so it was suspended instead of deleted.");
        }

        $stmt = $conn->prepare("DELETE FROM tbl_tier WHERE tier_id = ?");
        $stmt->bind_param("i", $tierId);
        $stmt->execute();
        redirect_admin("success", "Tier deleted.");
    }
}

$stats = ["revenue" => 0, "departures" => 0, "tickets_sold" => 0];
$result = $conn->query("SELECT COALESCE(SUM(amount_paid), 0) AS total FROM tbl_payment");
if ($result) $stats["revenue"] = (float) $result->fetch_assoc()["total"];
$result = $conn->query("SELECT COUNT(*) AS total FROM tbl_ticket WHERE status = 'active'");
if ($result) $stats["departures"] = (int) $result->fetch_assoc()["total"];
$result = $conn->query("SELECT COUNT(*) AS total FROM tbl_booking");
if ($result) $stats["tickets_sold"] = (int) $result->fetch_assoc()["total"];

$schedules = [];
$result = $conn->query(
    "SELECT ticket_no, cruise_ship, itinerary, arrival_date, departure_date, room_no, status
       FROM tbl_ticket
      ORDER BY cruise_ship, arrival_date"
);
if ($result) {
    while ($row = $result->fetch_assoc()) $schedules[] = $row;
}

$tiers = [];
$result = $conn->query("SELECT tier_id, tier_name, base_price, promo_price, status FROM tbl_tier ORDER BY tier_id");
if ($result) {
    while ($row = $result->fetch_assoc()) $tiers[] = $row;
}

$shipNames = [];
foreach ($schedules as $schedule) {
    if (!in_array($schedule["cruise_ship"], $shipNames, true)) $shipNames[] = $schedule["cruise_ship"];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title>Paglaot - Admin</title>
    <link rel="stylesheet" href="style.css" />
    <link rel="preconnect" href="https://fonts.googleapis.com" />
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet" />
</head>
<body>
<header class="topbar">
    <div class="topbar__brand"><span class="topbar__name">Paglaot</span></div>
    <div class="topbar__right">
        <span class="topbar__welcome">Welcome, Admin</span>
        <a href="../index.php" class="btn btn--outline btn--sm">Back to site</a>
    </div>
</header>

<div class="layout">
    <aside class="sidebar">
        <nav class="sidebar__nav">
            <a href="#" class="sidebar__link sidebar__link--active">Dashboard</a>
            <a href="#schedules" class="sidebar__link">Ships</a>
            <a href="#tiers" class="sidebar__link">Tiers</a>
            <a href="../index.php" class="sidebar__link sidebar__link--danger">Exit</a>
        </nav>
    </aside>

    <main class="main">
        <div class="page-header">
            <div>
                <h1 class="page-title">Dashboard</h1>
                <p class="page-sub">Manage ship calendars separately from tier prices</p>
            </div>
            <div class="page-header__date" id="live-date"></div>
        </div>

        <?php if ($message !== ""): ?>
            <div class="admin-alert admin-alert--<?php echo e($messageType); ?>"><?php echo e($message); ?></div>
        <?php endif; ?>

        <section class="stats-grid">
            <div class="stat-card"><span class="stat-card__label">Total Revenue</span><span class="stat-card__value"><?php echo peso($stats["revenue"]); ?></span><span class="stat-card__badge stat-card__badge--neutral">Recorded payments</span></div>
            <div class="stat-card"><span class="stat-card__label">Active Ship Dates</span><span class="stat-card__value"><?php echo (int) $stats["departures"]; ?></span><span class="stat-card__badge stat-card__badge--neutral">Bookable calendars</span></div>
            <div class="stat-card"><span class="stat-card__label">Tickets Sold</span><span class="stat-card__value"><?php echo (int) $stats["tickets_sold"]; ?></span><span class="stat-card__badge stat-card__badge--neutral">Booking rows</span></div>
        </section>

        <section class="panel">
            <div class="panel__header">
                <div><h2 class="panel__title">Ship Maintenance</h2><p class="panel__sub">Suspending a ship hides its calendar from new bookings</p></div>
            </div>
            <div class="ship-actions">
                <?php foreach ($shipNames as $ship): ?>
                    <form method="post" class="ship-action-form">
                        <input type="hidden" name="action" value="ship_status">
                        <input type="hidden" name="cruise_ship" value="<?php echo e($ship); ?>">
                        <strong><?php echo e($ship); ?></strong>
                        <button class="btn btn--sm btn--danger" name="status" value="suspended" type="submit">Suspend</button>
                        <button class="btn btn--sm btn--ghost" name="status" value="active" type="submit">Reactivate</button>
                    </form>
                <?php endforeach; ?>
            </div>
        </section>

        <section class="panel" id="schedules">
            <div class="panel__header">
                <div><h2 class="panel__title">Ship Calendars</h2><p class="panel__sub">Ships, itineraries, arrival dates, and departure dates</p></div>
                <button class="btn btn--primary" id="openAddScheduleBtn" type="button">+ Add Ship Date</button>
            </div>
            <div class="table-wrap">
                <table class="table" id="scheduleTable">
                    <thead><tr><th>Ship</th><th>Itinerary</th><th>Arrival</th><th>Departure</th><th>Room</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($schedules as $s): ?>
                            <tr data-ticket-no="<?php echo (int) $s["ticket_no"]; ?>" data-ship="<?php echo e($s["cruise_ship"]); ?>" data-itinerary="<?php echo e($s["itinerary"]); ?>" data-arrival="<?php echo e($s["arrival_date"]); ?>" data-departure="<?php echo e($s["departure_date"]); ?>" data-room="<?php echo e($s["room_no"]); ?>" data-status="<?php echo e($s["status"]); ?>">
                                <td class="table__name"><?php echo e($s["cruise_ship"]); ?></td>
                                <td><?php echo e($s["itinerary"] ?: "Unassigned"); ?></td>
                                <td><?php echo e($s["arrival_date"]); ?></td>
                                <td><?php echo e($s["departure_date"]); ?></td>
                                <td><?php echo e($s["room_no"]); ?></td>
                                <td><span class="badge badge--<?php echo e($s["status"]); ?>"><?php echo e(ucfirst($s["status"])); ?></span></td>
                                <td class="table__actions"><button class="btn btn--sm btn--ghost edit-schedule-btn" type="button">Edit</button><button class="btn btn--sm btn--danger delete-schedule-btn" type="button">Delete</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>

        <section class="panel" id="tiers">
            <div class="panel__header">
                <div><h2 class="panel__title">Tier Prices</h2><p class="panel__sub">Price depends on selected tier, not selected ship</p></div>
                <button class="btn btn--primary" id="openAddTierBtn" type="button">+ Add Tier</button>
            </div>
            <div class="table-wrap">
                <table class="table" id="tierTable">
                    <thead><tr><th>Tier</th><th>Base Price</th><th>Promo Price</th><th>Status</th><th style="width:160px">Actions</th></tr></thead>
                    <tbody>
                        <?php foreach ($tiers as $t): ?>
                            <tr data-tier-id="<?php echo (int) $t["tier_id"]; ?>" data-tier-name="<?php echo e($t["tier_name"]); ?>" data-base-price="<?php echo e($t["base_price"]); ?>" data-promo-price="<?php echo e($t["promo_price"]); ?>" data-status="<?php echo e($t["status"]); ?>">
                                <td class="table__name"><?php echo e($t["tier_name"]); ?></td>
                                <td class="table__price"><?php echo peso($t["base_price"]); ?></td>
                                <td><?php echo $t["promo_price"] === null ? "-" : peso($t["promo_price"]); ?></td>
                                <td><span class="badge badge--<?php echo e($t["status"]); ?>"><?php echo e(ucfirst($t["status"])); ?></span></td>
                                <td class="table__actions"><button class="btn btn--sm btn--ghost edit-tier-btn" type="button">Edit</button><button class="btn btn--sm btn--danger delete-tier-btn" type="button">Delete</button></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </main>
</div>

<div class="modal-backdrop" id="scheduleModal" role="dialog" aria-modal="true">
    <form class="modal" method="post">
        <input type="hidden" name="action" id="schedule-action" value="add_schedule">
        <input type="hidden" name="ticket_no" id="schedule-ticket-no">
        <div class="modal__header"><h3 class="modal__title" id="schedule-title">Ship Calendar</h3><button class="modal__close" data-close="scheduleModal" type="button">&times;</button></div>
        <div class="modal__body"><div class="form-grid">
            <label class="form-label">Ship Name<input type="text" class="input" name="cruise_ship" id="schedule-ship" required></label>
            <label class="form-label">Itinerary<input type="text" class="input" name="itinerary" id="schedule-itinerary"></label>
            <label class="form-label">Arrival Date<input type="date" class="input" name="arrival_date" id="schedule-arrival" required></label>
            <label class="form-label">Departure Date<input type="date" class="input" name="departure_date" id="schedule-departure" required></label>
            <label class="form-label">Room No.<input type="text" class="input" name="room_no" id="schedule-room" required></label>
            <label class="form-label">Status<select class="input" name="status" id="schedule-status"><option value="active">Active</option><option value="suspended">Suspended</option></select></label>
        </div></div>
        <div class="modal__footer"><button class="btn btn--outline" data-close="scheduleModal" type="button">Cancel</button><button class="btn btn--primary" type="submit">Save</button></div>
    </form>
</div>

<div class="modal-backdrop" id="tierModal" role="dialog" aria-modal="true">
    <form class="modal" method="post">
        <input type="hidden" name="action" id="tier-action" value="add_tier">
        <input type="hidden" name="tier_id" id="tier-id">
        <div class="modal__header"><h3 class="modal__title" id="tier-title">Tier Price</h3><button class="modal__close" data-close="tierModal" type="button">&times;</button></div>
        <div class="modal__body"><div class="form-grid">
            <label class="form-label">Tier Name<input type="text" class="input" name="tier_name" id="tier-name" required></label>
            <label class="form-label">Base Price<input type="number" step="0.01" min="0" class="input" name="base_price" id="tier-base-price" required></label>
            <label class="form-label">Promo Price<input type="number" step="0.01" min="0" class="input" name="promo_price" id="tier-promo-price"></label>
            <label class="form-label">Status<select class="input" name="status" id="tier-status"><option value="active">Active</option><option value="suspended">Suspended</option></select></label>
        </div></div>
        <div class="modal__footer"><button class="btn btn--outline" data-close="tierModal" type="button">Cancel</button><button class="btn btn--primary" type="submit">Save</button></div>
    </form>
</div>

<div class="modal-backdrop" id="deleteScheduleModal" role="dialog" aria-modal="true">
    <form class="modal modal--sm" method="post">
        <input type="hidden" name="action" value="delete_schedule"><input type="hidden" name="ticket_no" id="delete-schedule-id">
        <div class="modal__header"><h3 class="modal__title">Remove schedule?</h3><button class="modal__close" data-close="deleteScheduleModal" type="button">&times;</button></div>
        <div class="modal__body"><p class="modal__text">If this schedule has bookings, it will be suspended instead.</p><p class="modal__text"><strong id="delete-schedule-name"></strong></p></div>
        <div class="modal__footer"><button class="btn btn--outline" data-close="deleteScheduleModal" type="button">Cancel</button><button class="btn btn--danger" type="submit">Remove</button></div>
    </form>
</div>

<div class="modal-backdrop" id="deleteTierModal" role="dialog" aria-modal="true">
    <form class="modal modal--sm" method="post">
        <input type="hidden" name="action" value="delete_tier"><input type="hidden" name="tier_id" id="delete-tier-id">
        <div class="modal__header"><h3 class="modal__title">Remove tier?</h3><button class="modal__close" data-close="deleteTierModal" type="button">&times;</button></div>
        <div class="modal__body"><p class="modal__text">If this tier has bookings, it will be suspended instead.</p><p class="modal__text"><strong id="delete-tier-name"></strong></p></div>
        <div class="modal__footer"><button class="btn btn--outline" data-close="deleteTierModal" type="button">Cancel</button><button class="btn btn--danger" type="submit">Remove</button></div>
    </form>
</div>

<script src="script.js"></script>
</body>
</html>
