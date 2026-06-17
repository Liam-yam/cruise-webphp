<?php
require_once dirname(__DIR__, 2) . "/db.php";

class BookingData {
  public $siteName = "Paglaot";
  public $tagline = "Pearl of the Orient Sea";
  public $pageTitle = "Book Cruise";
  public $activePage = "Book a Cruise";
  public $bookingMessage = "";

  public $navLinks;
  public $ships = [];
  public $tripDates = [];
  public $tierPrices = [];
  public $promoPrices = [];
  public $packages = [];
  public $bookingSchedule = [];
  public $bookingJson = "{}";

  public $selectedShip = "";
  public $selectedTier = "";

  private $activeSchedules = [];
  private $activeTiers = [];

  public function __construct() {
    $this->navLinks = [
      "Our Ships" => "../ourships/LostCities.php",
      "Book a Cruise" => "booking.php",
      "Destinations" => "../destination/destination.php",
      "About" => "../../navbar/about.php"
    ];

    $this->activeSchedules = $this->loadActiveSchedules();
    $this->activeTiers = $this->loadActiveTiers();
    $this->buildBookingOptions();

    $this->selectedShip = $this->cleanText($_GET["ship"] ?? "");
    $this->selectedTier = $this->cleanText($_GET["tier"] ?? "");

    if (!in_array($this->selectedShip, $this->ships, true)) {
      $this->selectedShip = "";
    }

    if (!array_key_exists($this->selectedTier, $this->tierPrices)) {
      $this->selectedTier = "";
    }

    $this->handleBooking();
  }

  public function cleanText($value) {
    return trim($value);
  }

  public function formatPeso($amount) {
    return "&#8369;" . number_format((float) $amount);
  }

  public function getTierLabel($tier) {
    return $tier . " Cabin";
  }

  public function getBookingTotal($tier, $adults, $children) {
    $adultPrice = (float) ($this->tierPrices[$tier] ?? 0);
    return ($adults * $adultPrice) + ($children * ($adultPrice * 0.5));
  }

  private function loadActiveSchedules() {
    global $conn;

    $result = $conn->query(
      "SELECT ticket_no, cruise_ship, itinerary, arrival_date, departure_date, room_no
         FROM tbl_ticket
        WHERE status = 'active'
        ORDER BY cruise_ship, arrival_date"
    );

    if (!$result) {
      $this->bookingMessage = "Cruise schedules are not ready yet.";
      return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
      $row["trip_label"] = $this->formatTripLabel($row["arrival_date"], $row["departure_date"]);
      $rows[] = $row;
    }

    return $rows;
  }

  private function loadActiveTiers() {
    global $conn;

    $result = $conn->query(
      "SELECT tier_id, tier_name, base_price, promo_price
         FROM tbl_tier
        WHERE status = 'active'
        ORDER BY tier_id"
    );

    if (!$result) {
      $this->bookingMessage = "Tier prices are not ready yet.";
      return [];
    }

    $rows = [];
    while ($row = $result->fetch_assoc()) {
      $rows[] = $row;
    }

    return $rows;
  }

  private function buildBookingOptions() {
    foreach ($this->activeSchedules as $schedule) {
      $ship = $schedule["cruise_ship"];
      $label = $schedule["trip_label"];

      if (!in_array($ship, $this->ships, true)) {
        $this->ships[] = $ship;
      }

      if (!isset($this->tripDates[$ship])) {
        $this->tripDates[$ship] = [];
      }

      if (!in_array($label, $this->tripDates[$ship], true)) {
        $this->tripDates[$ship][] = $label;
      }

      $this->bookingSchedule[] = [
        "ticket_no" => (int) $schedule["ticket_no"],
        "ship" => $ship,
        "itinerary" => $schedule["itinerary"],
        "trip_label" => $label,
        "arrival_date" => $schedule["arrival_date"],
        "departure_date" => $schedule["departure_date"],
        "room_no" => $schedule["room_no"]
      ];
    }

    foreach ($this->activeTiers as $tier) {
      $tierName = $tier["tier_name"];
      $this->tierPrices[$tierName] = (float) $tier["base_price"];
      if ($tier["promo_price"] !== null) {
        $this->promoPrices[$tierName] = (float) $tier["promo_price"];
      }
    }

    sort($this->ships);
    $this->packages = $this->buildPackages(array_keys($this->tierPrices));
    $this->bookingJson = json_encode([
      "schedules" => $this->bookingSchedule,
      "tiers" => array_map(function ($tier) {
        return [
          "tier_id" => (int) $tier["tier_id"],
          "tier_name" => $tier["tier_name"],
          "base_price" => (float) $tier["base_price"],
          "promo_price" => $tier["promo_price"] === null ? null : (float) $tier["promo_price"]
        ];
      }, $this->activeTiers)
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT);
  }

  private function buildPackages($tiers) {
    $templates = [
      "Premium" => [
        "grade" => "B",
        "class" => "package-premium",
        "image" => "assets/premium.png",
        "alt" => "Premium cabin",
        "features" => [
          "Comfortable interior or basic ocean-view cabin",
          "Access to main dining & buffet",
          "Complimentary non-alcoholic drinks",
          "Access to pool, gym, and shows",
          "Daily housekeeping",
          "Basic onboard activities"
        ]
      ],
      "Elite" => [
        "grade" => "A",
        "class" => "package-elite",
        "image" => "assets/elite.png",
        "alt" => "Elite cabin",
        "features" => [
          "Spacious ocean-view cabin",
          "Priority check-in & boarding",
          "Access to premium dining",
          "Complimentary drinks",
          "Standard Wi-Fi",
          "Reserved seating for shows"
        ]
      ],
      "Ultimate" => [
        "grade" => "S",
        "class" => "package-royalty",
        "image" => "assets/royalty.png",
        "alt" => "Ultimate suite cabin",
        "features" => [
          "Luxury suite with balcony",
          "VIP priority boarding & exit",
          "Personal concierge",
          "Unlimited premium dining",
          "High-speed Wi-Fi",
          "24/7 room service"
        ]
      ]
    ];

    $packages = [];
    $fallbackImages = ["assets/premium.png", "assets/elite.png", "assets/royalty.png"];
    $index = 0;

    foreach ($tiers as $tier) {
      $packages[$tier] = $templates[$tier] ?? [
        "grade" => chr(66 + ($index % 3)),
        "class" => "package-premium",
        "image" => $fallbackImages[$index % count($fallbackImages)],
        "alt" => strtolower($tier) . " cabin",
        "features" => [
          "Cruise cabin assigned by schedule",
          "Dining and onboard activity access",
          "Daily housekeeping",
          "Standard guest support"
        ]
      ];
      $index++;
    }

    return $packages;
  }

  private function formatTripLabel($arrivalDate, $departureDate) {
    $arrival = strtotime($arrivalDate);
    $departure = strtotime($departureDate);

    if (!$arrival || !$departure) {
      return "";
    }

    if (date("Y-m", $arrival) === date("Y-m", $departure)) {
      return date("F j", $arrival) . " - " . date("j, Y", $departure);
    }

    if (date("Y", $arrival) === date("Y", $departure)) {
      return date("F j", $arrival) . " - " . date("F j, Y", $departure);
    }

    return date("F j, Y", $arrival) . " - " . date("F j, Y", $departure);
  }

  private function requireLogin() {
    if (!isset($_SESSION["user"])) {
      header("Location: ../login.php");
      exit();
    }
  }

  private function resolveUserId($conn) {
    $userEmail = $_SESSION["user_email"] ?? "";
    if ($userEmail === "") {
      return null;
    }

    $stmt = $conn->prepare(
      "SELECT u.user_id
         FROM tbl_user u
         JOIN tbl_registration r ON r.user_id = u.user_id
        WHERE r.email = ?
        LIMIT 1"
    );

    if (!$stmt) {
      return null;
    }

    $stmt->bind_param("s", $userEmail);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? (int) $row["user_id"] : null;
  }

  private function findSchedule($ship, $tripDate) {
    foreach ($this->activeSchedules as $schedule) {
      if ($schedule["cruise_ship"] === $ship && $schedule["trip_label"] === $tripDate) {
        return $schedule;
      }
    }

    return null;
  }

  private function findTier($tierName) {
    foreach ($this->activeTiers as $tier) {
      if ($tier["tier_name"] === $tierName) {
        return $tier;
      }
    }

    return null;
  }

  private function handlePayment() {
    global $conn;

    if (!isset($_POST["confirm_payment"])) {
      return false;
    }

    $this->requireLogin();
    $pendingBooking = $_SESSION["pending_booking"] ?? null;

    if (empty($pendingBooking)) {
      $this->bookingMessage = "Please complete the booking form before payment.";
      return true;
    }

    $paymentMethod = $this->cleanText($_POST["payment_method"] ?? "");
    $paymentReference = $this->getPaymentReference($paymentMethod);

    if ($paymentMethod === "" || $paymentReference === "") {
      $this->bookingMessage = "Please complete the payment details.";
      return true;
    }

    $userId = $this->resolveUserId($conn);
    $schedule = $this->findSchedule($pendingBooking["ship"], $pendingBooking["trip_date"]);
    $tier = $this->findTier($pendingBooking["tier"]);

    if (!$userId || !$schedule || !$tier) {
      $this->bookingMessage = "This cruise selection is no longer available.";
      return true;
    }

    $bookingStmt = $conn->prepare(
      "INSERT INTO tbl_booking (user_id, ticket_no, tier_id, adults, children, total_price, status, group_tag)
       VALUES (?, ?, ?, ?, ?, ?, ?, ?)"
    );
    $paymentStmt = $conn->prepare(
      "INSERT INTO tbl_payment (order_no, payment_method, ref_no, amount_paid, paid_date, paid_time)
       VALUES (?, ?, ?, ?, ?, ?)"
    );

    if (!$bookingStmt || !$paymentStmt) {
      $this->bookingMessage = "Payment cannot be processed until the booking and payment tables exist.";
      return true;
    }

    $adults = (int) $pendingBooking["adults"];
    $children = (int) $pendingBooking["children"];
    $total = (float) $pendingBooking["total"];
    $adultPrice = (float) $tier["base_price"];
    $childPrice = $adultPrice * 0.5;
    $guestCount = $adults + $children;
    $status = "paid";
    $paidDate = date("Y-m-d");
    $paidTime = date("H:i:s");
    $groupTag = "GRP-" . date("Ymd") . "-" . bin2hex(random_bytes(3));
    $firstOrderNo = null;
    $orderNumbers = [];

    $conn->begin_transaction();

    for ($i = 0; $i < $guestCount; $i++) {
      $isChild = ($i >= $adults);
      $guestAdults = $isChild ? 0 : 1;
      $guestChildren = $isChild ? 1 : 0;
      $guestTotal = $isChild ? $childPrice : $adultPrice;
      $ticketNo = (int) $schedule["ticket_no"];
      $tierId = (int) $tier["tier_id"];

      $bookingStmt->bind_param(
        "iiiiidss",
        $userId,
        $ticketNo,
        $tierId,
        $guestAdults,
        $guestChildren,
        $guestTotal,
        $status,
        $groupTag
      );

      if (!$bookingStmt->execute()) {
        $conn->rollback();
        $this->bookingMessage = "Booking failed. Please try again.";
        return true;
      }

      $orderNo = $conn->insert_id;
      $orderNumbers[] = $orderNo;
      if ($firstOrderNo === null) {
        $firstOrderNo = $orderNo;
      }
    }

    $paymentStmt->bind_param("issdss", $firstOrderNo, $paymentMethod, $paymentReference, $total, $paidDate, $paidTime);

    if (!$paymentStmt->execute()) {
      $conn->rollback();
      $this->bookingMessage = "Payment failed. Please try again.";
      return true;
    }

    $conn->commit();

    $_SESSION["paid_ticket"] = [
      "order_no" => $firstOrderNo,
      "order_numbers" => $orderNumbers,
      "user_id" => $userId,
      "ticket_no" => (int) $schedule["ticket_no"],
      "tier_id" => (int) $tier["tier_id"],
      "user_name" => $_SESSION["user"],
      "user_email" => $_SESSION["user_email"] ?? "",
      "ship" => $pendingBooking["ship"],
      "trip_date" => $pendingBooking["trip_date"],
      "departure_date" => $schedule["departure_date"],
      "tier" => $pendingBooking["tier"],
      "adults" => $adults,
      "children" => $children,
      "total" => $total,
      "payment_method" => $paymentMethod,
      "issued_at" => $paidDate . " " . $paidTime
    ];

    unset($_SESSION["pending_booking"]);
    header("Location: booking.php?ticket=success");
    exit();
  }

  private function getPaymentReference($paymentMethod) {
    switch ($paymentMethod) {
      case "GCash":
        $num = preg_replace("/\D/", "", $_POST["gcash_number"] ?? "");
        if (!preg_match('/^09[0-9]{9}$/', $num)) {
          $_SESSION["payment_error"] = "Invalid GCash number. It must be 11 digits starting with 09.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        $ref = $this->cleanText($_POST["gcash_reference"] ?? "");
        if (!preg_match('/^[0-9]{13}$/', $ref)) {
          $_SESSION["payment_error"] = "Invalid GCash reference number. It must be exactly 13 digits.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        return $ref;
      case "Maya":
        $num = preg_replace("/\D/", "", $_POST["maya_number"] ?? "");
        if (!preg_match('/^09[0-9]{9}$/', $num)) {
          $_SESSION["payment_error"] = "Invalid Maya number. It must be 11 digits starting with 09.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        $ref = $this->cleanText($_POST["maya_reference"] ?? "");
        if (!preg_match('/^[A-Za-z0-9]{12}$/', $ref)) {
          $_SESSION["payment_error"] = "Invalid Maya reference number. It must be exactly 12 alphanumeric characters.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        return $ref;
      case "BPI":
        $cardNumber = preg_replace("/\D/", "", $_POST["bpi_card_number"] ?? "");
        if (strlen($cardNumber) < 13 || strlen($cardNumber) > 19) {
          $_SESSION["payment_error"] = "Invalid BPI card number. It must be between 13 and 19 digits.";
          header("Location: booking.php?pay_error=1");
          exit();
        }
        return "Card ending " . substr($cardNumber, -4);
      case "Visa":
      case "Mastercard":
      case "JCB":
        $cardNumber = preg_replace("/\D/", "", $_POST["card_number"] ?? "");
        return strlen($cardNumber) >= 4 ? "Card ending " . substr($cardNumber, -4) : "";
      default:
        return "";
    }
  }

  private function handleBooking() {
    if (($_SERVER["REQUEST_METHOD"] ?? "GET") !== "POST") {
      return;
    }

    if ($this->handlePayment()) {
      return;
    }

    $this->requireLogin();

    $postedShip = $this->cleanText($_POST["cruise_ship"] ?? "");
    $postedDate = $this->cleanText($_POST["trip_date"] ?? "");
    $postedTier = $this->cleanText($_POST["tier"] ?? "");
    $adults = $this->cleanText($_POST["adults"] ?? "1");
    $children = $this->cleanText($_POST["children"] ?? "0");
    $schedule = $this->findSchedule($postedShip, $postedDate);
    $tier = $this->findTier($postedTier);

    if ($postedShip === "" || $postedDate === "" || $postedTier === "") {
      $this->bookingMessage = "Please complete the booking form first.";
    } elseif (!is_numeric($adults) || !is_numeric($children)) {
      $this->bookingMessage = "Guest count must be numeric.";
    } elseif (!$schedule || !$tier) {
      $this->bookingMessage = "Please choose an available ship, date, and tier.";
    } else {
      $adults = (int) $adults;
      $children = (int) $children;
      $total = $this->getBookingTotal($postedTier, $adults, $children);

      $_SESSION["pending_booking"] = [
        "ship" => $postedShip,
        "trip_date" => $postedDate,
        "departure_date" => $schedule["departure_date"],
        "tier" => $postedTier,
        "adults" => $adults,
        "children" => $children,
        "total" => $total
      ];

      $this->bookingMessage = "Review your booking and complete payment below.";
    }
  }
}

$booking = new BookingData();

$siteName = $booking->siteName;
$tagline = $booking->tagline;
$pageTitle = $booking->pageTitle;
$activePage = $booking->activePage;
$bookingMessage = $booking->bookingMessage;
$navLinks = $booking->navLinks;
$ships = $booking->ships;
$tripDates = $booking->tripDates;
$tierPrices = $booking->tierPrices;
$promoPrices = $booking->promoPrices;
$packages = $booking->packages;
$bookingJson = $booking->bookingJson;
$selectedShip = $booking->selectedShip;
$selectedTier = $booking->selectedTier;
$pendingBooking = $_SESSION["pending_booking"] ?? null;
$paidTicket = $_SESSION["paid_ticket"] ?? null;

if (!empty($paidTicket)) {
  unset($_SESSION["paid_ticket"]);
}

function formatPeso($amount) {
  global $booking;
  return $booking->formatPeso($amount);
}

function getTierLabel($tier) {
  global $booking;
  return $booking->getTierLabel($tier);
}
?>
