<?php
session_start();
require_once '../models.php';
require_once '../../db.php';

// Check if user is logged in
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

function formatTicketRow($row) {
    return [
        'id' => 'ORD-' . str_pad($row['order_no'], 5, '0', STR_PAD_LEFT),
        'ship' => $row['cruise_ship'],
        'date' => $row['trip_date'],
        'tier' => $row['tier'],
        'guests' => (int) $row['adults'] + (int) $row['children'],
        'price' => 'PHP ' . number_format((float) $row['total_price']),
        'payment' => $row['payment_method'],
        'status' => $row['status']
    ];
}

function fetchTickets($conn, $userEmail, $userName, $history = false) {
    $dateCondition = $history
        ? "(b.status = 'completed' OR b.departure_date < CURDATE())"
        : "(b.status = 'paid' AND b.departure_date >= CURDATE())";

    $sql = "SELECT b.order_no, b.cruise_ship, b.trip_date, b.departure_date, b.tier,
                   b.adults, b.children, b.total_price, b.status, p.payment_method
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
$siteName = "Alena";
$tagline = "Pearl of the Orient Sea";
$activePage = "Profile";

$navLinks = [
    "Our Ships" => "../ourships/LostCities.php",
    "Book a Cruise" => "../bookacruise/booking.php",
    "Destinations" => "../destination/destination.php",
    "Profile" => "profile.php",
    "About" => "../../index.php#about"
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0" />
    <title><?php echo $pageTitle; ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@600&family=DM+Sans:wght@400;500&display=swap" rel="stylesheet" />
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
            background: #f5f7fa;
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
            background: #ffffff;
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
            color: #0a1628;
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
            background: #ffffff;
            border-radius: 12px;
            padding: 24px;
            border-left: 5px solid #67B5D1;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.08);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .ticket-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.12);
        }

        .ticket-id {
            font-size: 0.8rem;
            color: #67B5D1;
            font-weight: 700;
            letter-spacing: 0.05em;
            margin-bottom: 8px;
        }

        .ticket-ship {
            font-family: 'Playfair Display', serif;
            font-size: 1.3rem;
            color: #0a1628;
            font-weight: 600;
            margin-bottom: 12px;
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
            color: #555e6e;
        }

        .ticket-detail-row span:first-child {
            font-weight: 500;
        }

        .ticket-detail-row span:last-child {
            color: #0a1628;
            font-weight: 600;
        }

        .ticket-price {
            font-family: 'Playfair Display', serif;
            font-size: 1.5rem;
            color: #67B5D1;
            font-weight: 600;
            text-align: right;
            padding-top: 16px;
            border-top: 1px solid #e8eaf0;
        }

        .ticket-action {
            margin-top: 16px;
        }

        .ticket-btn {
            width: 100%;
            padding: 10px;
            background: #67B5D1;
            color: #ffffff;
            border: none;
            border-radius: 8px;
            font-family: 'DM Sans', sans-serif;
            font-size: 0.85rem;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s ease;
        }

        .ticket-btn:hover {
            background: #4fa0be;
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
                        <div class="ticket-card">
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
                                <button class="ticket-btn">View Details</button>
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
                        <div class="ticket-card" style="border-left-color: #bbb; opacity: 0.85;">
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
                                <button class="ticket-btn" style="background: #bbb; cursor: not-allowed;" disabled>Completed</button>
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
    </script>
</body>
</html>
