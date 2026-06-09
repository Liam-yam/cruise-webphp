<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
require_once __DIR__ . '/../models.php';
require_once __DIR__ . '/../../db.php';

if (!isset($_SESSION['user'])) {
    header('Location: ../login.php');
    exit;
}

$userName = $_SESSION['user'];
$userEmail = $_SESSION['user_email'] ?? '';
$userInitial = strtoupper(substr($userName, 0, 1));

if (empty($userEmail)) {
    $emailStmt = $conn->prepare('SELECT email FROM users WHERE full_name = ? LIMIT 1');

    if ($emailStmt) {
        $emailStmt->bind_param('s', $userName);
        $emailStmt->execute();
        $emailResult = $emailStmt->get_result();

        if ($emailRow = $emailResult->fetch_assoc()) {
            $userEmail = $emailRow['email'];
            $_SESSION['user_email'] = $userEmail;
        }
    }
}

$activeTickets = [];
$historyTickets = [];
$profileMessage = "";

function getTicketConfig($ship) {
    $configs = [
        "Tropical" => [
            "image" => "../bookacruise/assets/tickets/tp-ticket.png",
            "theme" => "tropical",
            "to" => "Tropical Isles",
            "card_image" => "../../static/tropical.png"
        ],
        "Masquerade" => [
            "image" => "../bookacruise/assets/tickets/mq-ticket.png",
            "theme" => "masquerade",
            "to" => "Masquerade Bay",
            "card_image" => "../../static/masq.png"
        ],
        "Lost Cities" => [
            "image" => "../bookacruise/assets/tickets/lc-ticket.png",
            "theme" => "lost-cities",
            "to" => "Lost Cities",
            "card_image" => "../../static/lost.png"
        ]
    ];

    return $configs[$ship] ?? $configs["Lost Cities"];
}

function formatTicketRow($row) {
    $orderNo = (int) $row['order_no'];
    $ship = $row['cruise_ship'];
    $ticketConfig = getTicketConfig($ship);
    $guestCount = (int) $row['adults'] + (int) $row['children'];

    return [
        'id' => 'ORD-' . str_pad($orderNo, 5, '0', STR_PAD_LEFT),
        'order_no' => $orderNo,
        'ship' => $ship,
        'title' => 'Paglaot - ' . strtoupper($ship),
        'name' => $row['user_name'],
        'date' => $row['trip_date'],
        'departure_date' => $row['departure_date'],
        'tier' => $row['tier'],
        'ticket_type' => $row['tier'] . ' Tier',
        'guests' => $guestCount,
        'guests_label' => $guestCount . ' Guest' . ($guestCount === 1 ? '' : 's'),
        'price' => 'PHP ' . number_format((float) $row['total_price']),
        'payment' => $row['payment_method'],
        'status' => $row['status'],
        'from' => 'Manila Port',
        'to' => $ticketConfig['to'],
        'room' => 'D' . (($orderNo % 8) + 1) . '-' . str_pad((($orderNo % 900) + 100), 3, '0', STR_PAD_LEFT),
        'issued' => date('M d, Y h:i A', strtotime($row['paid_at'])),
                'image' => $ticketConfig['image'],
        'theme' => $ticketConfig['theme'],
        'card_image' => $ticketConfig['card_image'],
        'logo' => '../bookacruise/assets/tickets/logo.svg',
        'download' => 'paglaot-' . strtolower(str_replace(' ', '-', $ship)) . '-ticket-' . $orderNo . '.png'
    ];
}

function fetchTickets($conn, $userEmail, $userName, $history = false) {
    $dateCondition = $history
        ? "(b.status = 'completed' OR b.departure_date < CURDATE())"
        : "(b.status = 'paid' AND b.departure_date >= CURDATE())";

    $sql = "SELECT b.order_no, b.user_name, b.cruise_ship, b.trip_date, b.departure_date, b.tier,
                   b.adults, b.children, b.total_price, b.status, p.payment_method, p.paid_at
            FROM booking b
            INNER JOIN payment p ON p.order_no = b.order_no
            WHERE (b.user_email = ? OR (b.user_email = '' AND b.user_name = ?))
              AND $dateCondition
            ORDER BY b.departure_date " . ($history ? "DESC" : "ASC");

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        return null;
    }

    $stmt->bind_param('ss', $userEmail, $userName);
    $stmt->execute();

    $tickets = [];
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $tickets[] = formatTicketRow($row);
    }

    return $tickets;
}

$loadedActiveTickets = fetchTickets($conn, $userEmail, $userName, false);
$loadedHistoryTickets = fetchTickets($conn, $userEmail, $userName, true);

if ($loadedActiveTickets === null || $loadedHistoryTickets === null) {
    $profileMessage = "Booking and payment tables are not ready yet. Please run the new SQL command.";
} else {
    $activeTickets = $loadedActiveTickets;
    $historyTickets = $loadedHistoryTickets;
}

$pageTitle = "User Profile";
$siteName = "Paglaot";
$tagline = "Pearl of the Orient Sea";
$activePage = "Profile";

$navLinks = [
    "Our Ships"     => "../ourships/LostCities.php",
    "Book a Cruise" => "../bookacruise/booking.php",
    "Destinations"  => "../destination/destination.php",
    "About" => "../../navbar/about.php"
];

function e($value) {
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="../bookacruise/styles.css">
    <style>
        *,
        *::before,
        *::after {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

                                body {
            font-family: 'DM Sans', sans-serif;
            background: #0a1628;
            padding-top: 90px;
        }

        nav,
        nav * {
            font-family: 'DM Sans', sans-serif;
        }

        nav .logo-text {
            font-family: 'Playfair Display', serif;
        }

        nav .logo-text span {
            font-family: 'DM Sans', sans-serif;
        }

        nav {
            width: 100%;
            position: fixed;
            top: 0;
            z-index: 1000;
            background: rgba(14, 90, 130, 0.50);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.15);
        }

        .nav-inner {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 2rem;
            height: 90px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .logo-icon {
            width: 80px;
            height: 80px;
        }

        .logo-text {
            font-family: 'Playfair Display', serif;
            font-size: 1.25rem;
            color: #ffffff;
            letter-spacing: 0.03em;
            line-height: 1.2;
        }

        .logo-text span {
            display: block;
            font-size: 0.6rem;
            font-family: 'DM Sans', sans-serif;
            font-weight: 400;
            letter-spacing: 0.2em;
            text-transform: uppercase;
            color: rgba(255, 255, 255, 0.5);
        }

        .nav-links {
            display: flex;
            align-items: center;
            gap: 0.25rem;
            list-style: none;
        }

        .nav-links a {
            display: block;
            padding: 0.5rem 0.85rem;
            font-size: 0.875rem;
            font-weight: 400;
            color: rgba(255, 255, 255, 0.75);
            text-decoration: none;
            border-radius: 6px;
            transition: color 0.2s, background 0.2s;
            letter-spacing: 0.01em;
        }

        .nav-links a:hover {
            color: #ffffff;
            background: rgba(255, 255, 255, 0.08);
        }

        .nav-links a.active {
            color: #ffffff;
        }

        .btn-signin {
            padding: 0.5rem 1.25rem;
            font-size: 0.875rem;
            font-weight: 500;
            font-family: 'DM Sans', sans-serif;
            color: #ffffff;
            background: transparent;
            border: 1px solid rgba(255, 255, 255, 0.35);
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.2s, border-color 0.2s;
            letter-spacing: 0.02em;
        }

        .btn-signin:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: rgba(255, 255, 255, 0.6);
        }

        .hamburger {
            display: none;
            flex-direction: column;
            gap: 5px;
            cursor: pointer;
            padding: 4px;
            background: none;
            border: none;
        }

        .hamburger span {
            display: block;
            width: 22px;
            height: 2px;
            background: rgba(255, 255, 255, 0.8);
            border-radius: 2px;
            transition: all 0.3s;
        }

        .profile-container {
            max-width: 1000px;
            margin: 0 auto;
            padding: 40px 2rem;
        }

                .profile-card {
            background: #0f3061;
            border-radius: 16px;
            padding: 40px;
            text-align: center;
            box-shadow: 0 4px 24px rgba(0, 0, 0, 0.08);
            margin-bottom: 40px;
        }

        .profile-picture {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: linear-gradient(135deg, #67B5D1, #0e5a82);
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
            color: #ffffff;
            font-size: 2.5rem;
            font-weight: 700;
            box-shadow: 0 8px 24px rgba(103, 181, 209, 0.3);
        }

        .profile-name {
            font-family: 'Playfair Display', serif;
            font-size: 1.8rem;
            color: #e4e4e4;
            margin-bottom: 30px;
            font-weight: 600;
        }

        .profile-tabs {
            display: flex;
            gap: 16px;
            justify-content: center;
        }

        .tab-btn {
            padding: 12px 32px;
            border: 2px solid #dde1e9;
            background: #ffffff;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.95rem;
            font-weight: 600;
            color: #555e6e;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .tab-btn.active {
            background: #67B5D1;
            color: #ffffff;
            border-color: #67B5D1;
        }

        .tab-btn:hover {
            border-color: #67B5D1;
            color: #67B5D1;
        }

        .tab-btn.active:hover {
            background: #4fa0be;
            border-color: #4fa0be;
        }

        .tickets-section {
            display: none;
        }

        .tickets-section.active {
            display: block;
        }

        .tickets-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(320px, 1fr));
            gap: 24px;
            margin-top: 24px;
        }

                .ticket-card {
                    position: relative;
                    background: #0a1628;
                    background-size: cover;
                    background-position: center;
                    background-repeat: no-repeat;
                    border-radius: 12px;
                    padding: 0;
                    border-left: 5px solid #67B5D1;
                    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
                    transition: transform 0.3s ease, box-shadow 0.3s ease;
                    overflow: hidden;
                    min-height: 320px;
                }

                .ticket-card-overlay {
                    position: absolute;
                    top: 0;
                    left: 0;
                    right: 0;
                    bottom: 0;
                    background: rgba(10, 22, 40, 0.55);
                    pointer-events: none;
                    z-index: 1;
                }

                .ticket-card-content {
                    position: relative;
                    z-index: 2;
                    padding: 24px;
                    color: #ffffff;
                }

                .ticket-card-past {
                    opacity: 0.95;
                }

                .ticket-card-past .ticket-card-overlay {
                    background: rgba(10, 22, 40, 0.7);
                }

                .ticket-card:hover {
                    transform: translateY(-4px);
                    box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
                }

                .ticket-id {
                    font-size: 0.8rem;
                    color: #ffffff;
                    background: rgba(14, 90, 130, 0.85);
                    display: inline-block;
                    padding: 4px 10px;
                    border-radius: 4px;
                    font-weight: 700;
                    letter-spacing: 0.05em;
                    margin-bottom: 8px;
                }

                .ticket-ship {
                    font-family: 'Playfair Display', serif;
                    font-size: 1.3rem;
                    color: #ffffff;
                    font-weight: 600;
                    margin-bottom: 12px;
                    text-shadow: 0 2px 8px rgba(0, 0, 0, 0.6);
                }

        .ticket-details {
            display: flex;
            flex-direction: column;
            gap: 8px;
            margin-bottom: 16px;
        }

                .ticket-detail-row {
            display: flex;
            justify-content: space-between;
            font-size: 0.85rem;
            color: rgba(255, 255, 255, 0.85);
        }

        .ticket-detail-row span:first-child {
            font-weight: 500;
        }

        .ticket-detail-row span:last-child {
            color: #ffffff;
            font-weight: 600;
        }

        .ticket-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #ffffff;
            font-weight: 600;
            text-align: right;
            padding-top: 16px;
            border-top: 1px solid rgba(255, 255, 255, 0.25);
            text-shadow: 0 2px 8px rgba(0, 0, 0, 0.5);
        }

        .ticket-action {
            margin-top: 16px;
        }

                .ticket-btn {
            width: 100%;
            padding: 10px;
            background: rgba(255, 255, 255, 0.95);
            color: #0a1628;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease, transform 0.2s ease;
        }

        .ticket-btn:hover {
            background: #ffffff;
            transform: translateY(-1px);
        }

        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #888;
        }

        .empty-state-icon {
            font-size: 3rem;
            margin-bottom: 16px;
        }

        .empty-state-text {
            font-size: 1rem;
            margin-bottom: 8px;
        }

        .empty-state-sub {
            font-size: 0.9rem;
            color: #aaa;
        }

        .profile-message {
            margin-bottom: 24px;
            padding: 16px 20px;
            border-radius: 10px;
            background: #fff8e1;
            color: #775a00;
            font-weight: 600;
            text-align: center;
        }

        @media (max-width: 768px) {
            .nav-links,
            .btn-signin {
                display: none;
            }

            .hamburger {
                display: flex;
            }

            .profile-container {
                padding: 24px 1rem;
            }

            .profile-card {
                padding: 24px;
            }

            .profile-tabs {
                flex-direction: column;
            }

            .tab-btn {
                width: 100%;
            }

            .tickets-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div class="nav-inner">

            <a href="../../index.php" class="logo">
                <img src="../../static/logo.svg" alt="Logo" class="logo-icon">

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

                    <a href="profile.php" style="text-decoration:none;color:inherit;">

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

    <div class="profile-container">
        <div class="profile-card">
            <div class="profile-picture"><?php echo $userInitial; ?></div>
            <h1 class="profile-name"><?php echo htmlspecialchars($userName); ?></h1>
            
            <div class="profile-tabs">
                <button class="tab-btn active" onclick="switchTab('tickets', event)">Tickets</button>
                <button class="tab-btn" onclick="switchTab('history', event)">History</button>
            </div>
        </div>

        <?php if (!empty($profileMessage)): ?>
            <div class="profile-message">
                <?php echo htmlspecialchars($profileMessage); ?>
            </div>
        <?php endif; ?>

        <!-- ACTIVE TICKETS -->
        <div id="tickets" class="tickets-section active">
            <div class="tickets-grid">
                <?php if (!empty($activeTickets)): ?>
                    <?php foreach ($activeTickets as $ticket): ?>
                                                <div class="ticket-card" style="background-image: url('<?php echo htmlspecialchars($ticket['card_image']); ?>');">
                            <div class="ticket-card-overlay"></div>
                            <div class="ticket-card-content">
                            <div class="ticket-id"><?php echo htmlspecialchars($ticket['id']); ?></div>
                            <div class="ticket-ship"><?php echo htmlspecialchars($ticket['ship']); ?></div>
                            <div class="ticket-details">
                                <div class="ticket-detail-row">
                                    <span>Date:</span>
                                    <span><?php echo htmlspecialchars($ticket['date']); ?></span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span>Tier:</span>
                                    <span><?php echo htmlspecialchars($ticket['tier']); ?></span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span>Guests:</span>
                                    <span><?php echo htmlspecialchars($ticket['guests']); ?></span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span>Payment:</span>
                                    <span><?php echo htmlspecialchars($ticket['payment']); ?></span>
                                </div>
                            </div>
                            <div class="ticket-price"><?php echo htmlspecialchars($ticket['price']); ?></div>
                            <div class="ticket-action">
                                <button type="button"
                                        class="ticket-btn"
                                        data-ticket-open
                                        data-ticket-image="<?php echo e($ticket['image']); ?>"
                                        data-ticket-title="<?php echo e($ticket['title']); ?>"
                                        data-ticket-ship="<?php echo e(strtoupper($ticket['ship'])); ?>"
                                        data-ticket-name="<?php echo e($ticket['name']); ?>"
                                        data-ticket-type="<?php echo e($ticket['ticket_type']); ?>"
                                        data-ticket-from="<?php echo e($ticket['from']); ?>"
                                        data-ticket-to="<?php echo e($ticket['to']); ?>"
                                        data-ticket-room="<?php echo e($ticket['room']); ?>"
                                        data-ticket-issued="<?php echo e($ticket['issued']); ?>"
                                        data-ticket-departure="<?php echo e($ticket['departure_date']); ?>"
                                        data-ticket-order="<?php echo e($ticket['id']); ?>"
                                        data-ticket-guests="<?php echo e($ticket['guests_label']); ?>"
                                        data-ticket-logo="<?php echo e($ticket['logo']); ?>"
                                        data-ticket-download="<?php echo e($ticket['download']); ?>"
                                        data-ticket-theme="<?php echo e($ticket['theme']); ?>">
                                                                        View Details
                                </button>
                            </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">🎫</div>
                        <div class="empty-state-text">No Active Tickets</div>
                        <div class="empty-state-sub">Book a cruise to get started</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- TICKET HISTORY -->
        <div id="history" class="tickets-section">
            <div class="tickets-grid">
                <?php if (!empty($historyTickets)): ?>
                    <?php foreach ($historyTickets as $ticket): ?>
                                                <div class="ticket-card ticket-card-past" style="background-image: url('<?php echo htmlspecialchars($ticket['card_image']); ?>');">
                            <div class="ticket-card-overlay"></div>
                            <div class="ticket-card-content">
                            <div class="ticket-id"><?php echo htmlspecialchars($ticket['id']); ?></div>
                            <div class="ticket-ship"><?php echo htmlspecialchars($ticket['ship']); ?></div>
                            <div class="ticket-details">
                                <div class="ticket-detail-row">
                                    <span>Date:</span>
                                    <span><?php echo htmlspecialchars($ticket['date']); ?></span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span>Tier:</span>
                                    <span><?php echo htmlspecialchars($ticket['tier']); ?></span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span>Guests:</span>
                                    <span><?php echo htmlspecialchars($ticket['guests']); ?></span>
                                </div>
                                <div class="ticket-detail-row">
                                    <span>Payment:</span>
                                    <span><?php echo htmlspecialchars($ticket['payment']); ?></span>
                                </div>
                            </div>
                            <div class="ticket-price"><?php echo htmlspecialchars($ticket['price']); ?></div>
                            <div class="ticket-action">
                                <button type="button"
                                        class="ticket-btn"
                                        data-ticket-open
                                        data-ticket-image="<?php echo e($ticket['image']); ?>"
                                        data-ticket-title="<?php echo e($ticket['title']); ?>"
                                        data-ticket-ship="<?php echo e(strtoupper($ticket['ship'])); ?>"
                                        data-ticket-name="<?php echo e($ticket['name']); ?>"
                                        data-ticket-type="<?php echo e($ticket['ticket_type']); ?>"
                                        data-ticket-from="<?php echo e($ticket['from']); ?>"
                                        data-ticket-to="<?php echo e($ticket['to']); ?>"
                                        data-ticket-room="<?php echo e($ticket['room']); ?>"
                                        data-ticket-issued="<?php echo e($ticket['issued']); ?>"
                                        data-ticket-departure="<?php echo e($ticket['departure_date']); ?>"
                                        data-ticket-order="<?php echo e($ticket['id']); ?>"
                                        data-ticket-guests="<?php echo e($ticket['guests_label']); ?>"
                                        data-ticket-logo="<?php echo e($ticket['logo']); ?>"
                                        data-ticket-download="<?php echo e($ticket['download']); ?>"
                                        data-ticket-theme="<?php echo e($ticket['theme']); ?>">
                                                                        View Details
                                </button>
                            </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="empty-state">
                        <div class="empty-state-icon">📋</div>
                        <div class="empty-state-text">No Past Cruises</div>
                        <div class="empty-state-sub">Your completed cruises will appear here</div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="ticket-backdrop" id="ticketBackdrop" aria-hidden="true">
        <div class="ticket-shell">
            <button type="button"
                    class="ticket-close"
                    data-ticket-close
                    aria-label="Close ticket">
                &times;
            </button>

            <article class="boarding-ticket" id="paidTicket">
                <div class="ticket-ribbon">BOARDING PASS</div>

                <div class="ticket-photo" data-ticket-photo>
                    <h2 data-ticket-text="title"></h2>
                </div>

                <div class="ticket-info">
                    <img src="../bookacruise/assets/tickets/logo.svg"
                         alt=""
                         class="ticket-watermark"
                         data-ticket-logo-img
                         aria-hidden="true">

                    <div class="ticket-info-top">
                        <h3 data-ticket-text="ship"></h3>
                        <div class="ticket-barcode" aria-hidden="true"></div>
                    </div>

                    <div class="ticket-field wide">
                        <span>Name</span>
                        <strong data-ticket-text="name"></strong>
                    </div>

                    <div class="ticket-field">
                        <span>Ticket Type</span>
                        <strong data-ticket-text="type"></strong>
                    </div>

                    <div class="ticket-route">
                        <div class="ticket-field">
                            <span>From</span>
                            <strong data-ticket-text="from"></strong>
                        </div>

                        <div class="ticket-field">
                            <span>To</span>
                            <strong data-ticket-text="to"></strong>
                        </div>
                    </div>

                    <div class="ticket-field room">
                        <span>Room Number:</span>
                        <strong data-ticket-text="room"></strong>
                    </div>

                    <p>Date and Time Issued: <strong data-ticket-text="issued"></strong></p>
                    <p>Departure Date: <strong data-ticket-text="departure"></strong></p>
                    <p>Order No: <strong data-ticket-text="order"></strong></p>
                    <p>Guests: <strong data-ticket-text="guests"></strong></p>
                </div>
            </article>

            <button type="button" class="ticket-download" id="ticketDownload">
                Download
            </button>
        </div>
    </div>

    <script>
        function switchTab(tab, event) {
            // Hide all sections
            document.querySelectorAll('.tickets-section').forEach(section => {
                section.classList.remove('active');
            });
            document.querySelectorAll('.tab-btn').forEach(btn => {
                btn.classList.remove('active');
            });

            // Show selected section
            document.getElementById(tab).classList.add('active');
            event.target.classList.add('active');
        }

        document.addEventListener('DOMContentLoaded', () => {
            const ticketBackdrop = document.getElementById('ticketBackdrop');
            const paidTicket = document.getElementById('paidTicket');
            const ticketPhoto = document.querySelector('[data-ticket-photo]');
            const ticketLogoImage = document.querySelector('[data-ticket-logo-img]');
            const ticketClose = document.querySelector('[data-ticket-close]');
            const ticketDownload = document.getElementById('ticketDownload');
            const openButtons = document.querySelectorAll('[data-ticket-open]');

            function setTicketText(key, value) {
                const target = document.querySelector(`[data-ticket-text="${key}"]`);

                if (target) {
                    target.textContent = value || '';
                }
            }

            function openTicket(button) {
                const data = button.dataset;

                paidTicket.dataset.ticketImage = data.ticketImage;
                paidTicket.dataset.ticketTitle = data.ticketTitle;
                paidTicket.dataset.ticketShip = data.ticketShip;
                paidTicket.dataset.ticketName = data.ticketName;
                paidTicket.dataset.ticketType = data.ticketType;
                paidTicket.dataset.ticketFrom = data.ticketFrom;
                paidTicket.dataset.ticketTo = data.ticketTo;
                paidTicket.dataset.ticketRoom = data.ticketRoom;
                paidTicket.dataset.ticketIssued = data.ticketIssued;
                paidTicket.dataset.ticketDeparture = data.ticketDeparture;
                paidTicket.dataset.ticketOrder = data.ticketOrder;
                paidTicket.dataset.ticketGuests = data.ticketGuests;
                paidTicket.dataset.ticketLogo = data.ticketLogo;
                paidTicket.dataset.ticketDownload = data.ticketDownload;

                setTicketText('title', data.ticketTitle);
                setTicketText('ship', data.ticketShip);
                setTicketText('name', data.ticketName);
                setTicketText('type', data.ticketType);
                setTicketText('from', data.ticketFrom);
                setTicketText('to', data.ticketTo);
                setTicketText('room', data.ticketRoom);
                setTicketText('issued', data.ticketIssued);
                setTicketText('departure', data.ticketDeparture);
                setTicketText('order', data.ticketOrder);
                setTicketText('guests', data.ticketGuests);

                ticketPhoto.style.backgroundImage = `url('${data.ticketImage}')`;
                ticketLogoImage.src = data.ticketLogo;
                ticketBackdrop.className = `ticket-backdrop visible ticket-theme-${data.ticketTheme}`;
                ticketBackdrop.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }

            function closeTicket() {
                ticketBackdrop.classList.remove('visible');
                ticketBackdrop.setAttribute('aria-hidden', 'true');
                document.body.style.overflow = '';
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
                if (typeof context.roundRect === 'function') {
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
                context.fillStyle = '#3b3b3b';
                context.font = '24px Arial';
                context.fillText(label, x, y);

                context.fillStyle = '#d8d8d8';
                context.strokeStyle = '#2b77a8';
                context.lineWidth = 1;
                context.beginPath();
                roundedRect(context, x, y + 8, width, 34, 9);
                context.fill();
                context.stroke();

                context.fillStyle = '#183c5a';
                context.font = 'bold 15px Arial';
                context.fillText(value, x + 12, y + 31);
            }

            function drawBarcode(context, x, y, width, height) {
                context.fillStyle = '#111111';

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

            async function downloadTicketPng() {
                const data = paidTicket.dataset;

                if (!data.ticketImage) {
                    return;
                }

                const canvas = document.createElement('canvas');
                canvas.width = 1203;
                canvas.height = 487;

                const context = canvas.getContext('2d');
                const image = await loadTicketImage(data.ticketImage);
                const logo = await loadTicketImage(data.ticketLogo);

                context.fillStyle = '#ffffff';
                context.fillRect(0, 0, canvas.width, canvas.height);

                context.fillStyle = '#2b5d7c';
                context.fillRect(0, 0, 117, 487);

                context.save();
                context.translate(58, 244);
                context.rotate(-Math.PI / 2);
                context.fillStyle = '#ffffff';
                context.font = 'bold italic 48px Georgia';
                context.textAlign = 'center';
                context.fillText('BOARDING PASS', 0, 16);
                context.restore();

                if (image) {
                    drawCoverImage(context, image, 117, 0, 641, 487);
                    context.fillStyle = 'rgba(9, 29, 54, 0.28)';
                    context.fillRect(117, 0, 641, 487);
                } else {
                    context.fillStyle = '#1a3a5c';
                    context.fillRect(117, 0, 641, 487);
                }

                context.fillStyle = '#ffffff';
                context.font = 'bold italic 28px Georgia';
                context.textAlign = 'left';
                context.fillText(data.ticketTitle, 138, 48);

                context.fillStyle = '#ffffff';
                context.fillRect(758, 0, 445, 487);

                if (logo) {
                    context.save();
                    context.globalAlpha = 0.09;
                    context.drawImage(logo, 892, 100, 250, 250);
                    context.restore();
                }

                context.fillStyle = '#2b5d7c';
                fitText(context, data.ticketShip, 790, 58, 178, 28, 'Georgia');
                drawBarcode(context, 986, 16, 210, 60);

                drawTextField(context, 'Name', data.ticketName, 780, 132, 370);
                drawTextField(context, 'Ticket Type', data.ticketType, 780, 202, 210);
                drawTextField(context, 'From', data.ticketFrom, 780, 282, 160);
                drawTextField(context, 'To', data.ticketTo, 990, 282, 150);

                context.fillStyle = '#3b3b3b';
                context.font = '22px Arial';
                context.fillText('Room Number:', 780, 382);
                context.fillStyle = '#d8d8d8';
                context.strokeStyle = '#2b77a8';
                context.beginPath();
                roundedRect(context, 980, 354, 112, 34, 9);
                context.fill();
                context.stroke();
                context.fillStyle = '#183c5a';
                context.font = 'bold 15px Arial';
                context.fillText(data.ticketRoom, 992, 377);

                context.fillStyle = '#151515';
                context.font = '18px Arial';
                context.fillText(`Date and Time Issued: ${data.ticketIssued}`, 780, 426);
                context.fillText(`Departure Date: ${data.ticketDeparture}`, 780, 458);

                const link = document.createElement('a');
                link.href = canvas.toDataURL('image/png');
                link.download = data.ticketDownload || 'paglaot-ticket.png';
                link.click();
            }

            openButtons.forEach(button => {
                button.addEventListener('click', () => openTicket(button));
            });

            ticketClose.addEventListener('click', closeTicket);
            ticketBackdrop.addEventListener('click', event => {
                if (event.target === ticketBackdrop) {
                    closeTicket();
                }
            });
            ticketDownload.addEventListener('click', downloadTicketPng);
        });
    </script>
</body>
</html>
